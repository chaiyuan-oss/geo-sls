<?php
define('WP_MAX_MEMORY_LIMIT', '5120M');


function generateSql($data, $out_dir)
{
    $i = 0;

    $data = json_decode($data, true);
    $ext_name = $data['payload']['objects']['collection']['geometries'][0]['properties']['name'];
    $name = preg_replace("/市|盟|林区|藏族|苗族|布依族|侗族|土家族|黎族|朝鲜族|蒙古族|羌族|彝族|哈尼族|壮族|傣族|白族|傈僳族|县|景颇族|回族|维吾尔|自治州|自治县|地区|特别行政区|省|自治区|/i", "", $ext_name);
    $adcode = $data['payload']['objects']['collection']['geometries'][0]['properties']['adcode'];
    $center = $data['payload']['objects']['collection']['geometries'][0]['properties']['centroid'];
    if (empty($center)) {
        $center = $data['payload']['objects']['collection']['geometries'][0]['properties']['center'];
    }
    $point = "POINT(" . implode(' ', $center) . ")";
    $level = $data['payload']['objects']['collection']['geometries'][0]['properties']['level'];
    $arcs = $data['payload']['arcs'];
    foreach ($arcs as $value) {
        $arr = array();
        foreach ($value as $v) {
            $arr[] = implode(' ', $v);
        }
        if ($arr[0] != $arr[count($arr) - 1]) {
            $arr[] = $arr[0];
        }
        $arr_length = count($arr);
        $sub_length = ceil($arr_length / 3);
        $sub_array = array_chunk($arr, $sub_length);
        $polygon_sub1 = implode(',', $sub_array[0]);
        $polygon_sub2 = implode(',', isset($sub_array[2]) ? $sub_array[2] : array());
        $polygon_sub3 = implode(',', isset($sub_array[2]) ? $sub_array[2] : array());
        switch ($level) {
            case 'province':
                $level = 1;
                break;
            case 'city':
                $level = 2;
                break;
            case 'district':
                $level = 3;
                break;
            default:
                $level = 0;
        }
        $parent = $data['payload']['objects']['collection']['geometries'][0]['properties']['parent']['adcode'];
        $json = array('code' => $adcode, 'parent_code' => $parent, 'deep' => $level, 'name' => $name, 'ext_path' => $ext_name, 'point' => $point, 'polygon_sub1' => $polygon_sub1, 'polygon_sub2' => $polygon_sub2, 'polygon_sub3' => $polygon_sub3);
        $json = json_encode($json);
        file_put_contents($out_dir . "city_geo_gcj02.json", $json . PHP_EOL, FILE_APPEND);
        //直辖市 将省级数据转为城市数据 deep 1 => 2
        if (in_array($name, array("北京", "天津", "上海", "重庆"))) {//,"香港","澳门","台湾
            //公司数据库中的code尾数为100 则为直辖市
            $parent = $adcode;
            $adcode = substr_replace($adcode, 1, -3, 1);
            $json = array('code' => $adcode, 'parent_code' => $parent, 'deep' => 2, 'name' => $name, 'ext_path' => $ext_name, 'point' => $point, 'polygon_sub1' => $polygon_sub1, 'polygon_sub2' => $polygon_sub2, 'polygon_sub3' => $polygon_sub3);
            $json = json_encode($json);
            file_put_contents($out_dir . "city_geo_gcj02.json", $json . PHP_EOL, FILE_APPEND);
        }
    }


//直辖市直接区县 ,省份文件读取城市则截止，如需区县数据请自行扩展
    $children = $data['children'];
    foreach ($children as $k => $value) {
        $city_ext_name = $value['payload']['objects']['collection']['geometries'][0]['properties']['name'];
        $city_name = preg_replace("/市|盟|林区|藏族|苗族|布依族|侗族|土家族|黎族|朝鲜族|蒙古族|羌族|彝族|哈尼族|壮族|傣族|白族|傈僳族|县|景颇族|回族|维吾尔|自治州|自治县|地区|特别行政区|省|自治区|/i", "", $city_ext_name);
        $city_adcode = $value['payload']['objects']['collection']['geometries'][0]['properties']['adcode'];
//        $code = array(
//            '巢湖' => 341400,
//            '莱芜' => 371200,
//            '铜仁' => 522200,
//            '毕节' => 522400,
//            '昌都' => 542100,
//            '山南' => 542200,
//            '日喀则' => 542300,
//            '那曲' => 542400,
//            '林芝' => 542600,
//            '海东' => 632100,
//
//        );
//        if (isset($code[$city_name])) {
//            $city_adcode = $code[$city_name];
//        }
        $city_center = $value['payload']['objects']['collection']['geometries'][0]['properties']['centroid'];
        if (empty($city_center)) {
            $city_center = $value['payload']['objects']['collection']['geometries'][0]['properties']['center'];
        }
        $city_point = "POINT(" . implode(' ', $city_center) . ")";
        $city_level = $value['payload']['objects']['collection']['geometries'][0]['properties']['level'];
        if ($city_level != 'city') {
            continue;
        }
        $city_arcs = $value['payload']['arcs'];
        $i += count($city_arcs);
        echo $city_name . "已完成 共 ".count($city_arcs)." 条数据  ".json_encode(array($city_name))." \r\n";
        switch ($city_level) {
            case 'province':
                $city_level = 1;
                break;
            case 'city':
                $city_level = 2;
                break;
            case 'district':
                $city_level = 3;
                break;
            default:
                $city_level = 0;
        }
        $city_parent_array = $value['payload']['objects']['collection']['geometries'][0]['properties']['parent'];
        if (!is_array($city_parent_array)) {
            $city_parent_array = json_decode($city_parent_array, true);
        }
        $city_parent = $city_parent_array['adcode'];
        foreach ($city_arcs as  $val) {
            $city_arr = array();
            foreach ($val as $v) {
                $city_arr[] = implode(' ', $v);
            }
            if ($city_arr[0] != $city_arr[count($city_arr) - 1]) {
                $city_arr[] = $city_arr[0];
            }
            $city_arr_length = count($city_arr);
            $city_sub_length = ceil($city_arr_length / 3);
            $city_sub_array = array_chunk($city_arr, $city_sub_length);
            $city_polygon_sub1 = implode(',', $city_sub_array[0]);
            $city_polygon_sub2 = implode(',', isset($city_sub_array[1]) ? $city_sub_array[1] : array());
            $city_polygon_sub3 = implode(',', isset($city_sub_array[2]) ? $city_sub_array[2] : array());
            $json = array('code' => $city_adcode, 'parent_code' => $city_parent, 'deep' => $city_level, 'name' => $city_name, 'ext_path' => $city_ext_name, 'point' => $city_point, 'polygon_sub1' => $city_polygon_sub1, 'polygon_sub2' => $city_polygon_sub2, 'polygon_sub3' => $city_polygon_sub3);
            $json = json_encode($json);
            if ($city_level == 2) {
                file_put_contents($out_dir . "city_geo_gcj02.json", $json . PHP_EOL, FILE_APPEND);
            }
        }
    }

    return $i;
}

$out_dir = './sql/';
if (!is_dir($out_dir)) {
    mkdir($out_dir, 0777, true);
}
$in_dir = './json/';
$count = 0;
if (is_dir($in_dir)) {
    if ($dh = opendir($in_dir)) {
        while (($file = readdir($dh)) !== false) {
            $file_array = explode(".", basename($file));
            if ($file_array[1] == 'json') {
                $data = file_get_contents($in_dir . $file);
                $count+=generateSql($data, $out_dir);
            }
        }
        closedir($dh);
    }
} else {
    echo "输入文件夹错误!!!";
    die;
}
echo $count;
# geo-sls
Using Alibaba Cloud sls service to obtain city information by latitude and longitude

- geojson.php 

    `php executable file`

- sql 

    `Output directory, PHP script file execution results here`

The json files required for the script are in the [geo-data-TopoJSON](https://github.com/chaiyuan-oss/geo-data-TopoJSON) project



`console> php geojson.php`



[阿里云SLS日志服务](https://www.aliyun.com/product/sls?spm=5176.28508143.J_XmGx2FZCDAeIy2ZCWL7sW.1.e939154aebJbvW&scm=20140722.S_product@@%E4%BA%91%E4%BA%A7%E5%93%81@@99653._.ID_product@@%E4%BA%91%E4%BA%A7%E5%93%81@@99653-RL_sls-LOC_topbar~UND~product-OR_ser-V_3-P0_0)

SLS service data upload I choose JSON, you can refer to the specific document

Here is a statement for reference

```
* | SELECT code, name, ext_path, parent_code where ST_Contains( ST_GeometryFromText( CONCAT( 'POLYGON((', polygon_sub1, IF(polygon_sub2 != '', ',', ''),IF(polygon_sub2 != '', polygon_sub2, ''), IF(polygon_sub3 != '', ',', ''),IF(polygon_sub3 != '', polygon_sub3, ''),'))' ) ), ST_GeometryFromText('POINT($longitude $latitude)') ) AND deep = 2
```

I used the CONCAT function and the IF function to connect the polygon data I split, because SLS service single field (text type) has an upper limit, which does not meet my needs, so I divided it into three parts and connected them together

I use SLS service to achieve this requirement because MySQL cannot meet the concurrency requirement for my needs, and the cost of es is relatively high, so I choose SLS service, which is relatively low in cost. Of course, if the budget is sufficient, I can directly call the corresponding API of Baidu and Amap


<?php
use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if (!defined('ABSPATH')) exit;

class Taiwan_Temple_Map_Widget extends Widget_Base {
    public function get_name() {
        return 'taiwan_temple_map';
    }
    public function get_title() {
        return '台湾寺院マップ';
    }
    public function get_icon() {
        return 'eicon-google-maps';
    }
    public function get_categories() {
        return ['general'];
    }
    public function render() {
        // wp-config.php で define('GOOGLE_MAPS_API_KEY', 'xxxx'); と設定してください
        $api_key = defined('GOOGLE_MAPS_API_KEY') ? GOOGLE_MAPS_API_KEY : '';
        ?>
        <div id="slideb">
            <form>
                <p>
                    <span>エリア拡大ボタン➡</span>
                    <input type="button" value="台湾全体" onclick="toTaiwan()" />
                    <input type="button" value="台湾北部" onclick="toTN()" />
                    <input type="button" value="台湾中西部" onclick="toTC()" />
                    <input type="button" value="台湾中東部" onclick="toTE()" />
                    <input type="button" value="台湾南部" onclick="toTS()" />
                    <input type="button" value="日本" onclick="toJapan()" />
                    <input type="button" value="タイ" onclick="toThai()" />
                    <input type="button" value="アラスカ" onclick="toAlaska()" />
                </p>
            </form>
        </div>
        <div id="map-canvas" style="width:100%;height:500px;"></div>
        <?php
        $posts = get_posts([
            'posts_per_page' => -1,
            'post_type' => 'post',
            'meta_query' => [
                [
                    'key' => 'geo_latitude',
                    'compare' => '!=',
                    'value' => ''
                ]
            ]
        ]);
        $markerData = [];
        foreach ($posts as $post) {
            $lat = get_post_meta($post->ID, 'geo_latitude', true);
            $lng = get_post_meta($post->ID, 'geo_longitude', true);
            if ($lat && $lng) {
                $markerData[] = [
                    'name' => esc_js(get_the_title($post)),
                    'lat' => (float)$lat,
                    'lng' => (float)$lng,
                    'url' => get_permalink($post)
                ];
            }
        }
        ?>
        <script src="//maps.googleapis.com/maps/api/js?key=<?php echo esc_attr($api_key); ?>&libraries=places,geometry,places"></script>
        <script>
        var marker = [], infoWindow = [];
        var mapDiv = document.getElementById("map-canvas");
        var opts = {
            center: new google.maps.LatLng(23.823538, 121.030859),
            zoom: 7
        };
        var map = new google.maps.Map(mapDiv, opts);
        var markerData = <?php echo json_encode($markerData, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); ?>;
        var centerLat = 0, centerLng = 0;
        for (var i = 0; i < markerData.length; i++) {
            centerLat += markerData[i]['lat'];
            centerLng += markerData[i]['lng'];
            var markerLatLng = new google.maps.LatLng({
                lat: markerData[i]['lat'],
                lng: markerData[i]['lng']
            });
            marker[i] = new google.maps.Marker({
                position: markerLatLng,
                map: map
            });
            infoWindow[i] = new google.maps.InfoWindow({
                content: '<div class="sample"><a href="'+markerData[i]['url']+'">' + markerData[i]['name'] + '</a></div>'
            });
            markerEvent(i);
        }
        function markerEvent(i) {
            marker[i].addListener('click', function() {
                for(var s = 0; s<markerData.length; s++){
                    infoWindow[s].close();
                }
                infoWindow[i].open(map, marker[i]);
            });
        }
        function toJapan() {
            map.panTo(new google.maps.LatLng(37.413436, 139.934781));
            map.setZoom(5);
        }
        function toTaiwan() {
            map.panTo(new google.maps.LatLng(23.823538, 121.030859));
            map.setZoom(7);
        }
        function toTN() {
            map.panTo(new google.maps.LatLng(25.0169639,121.2261857));
            map.setZoom(10);
        }
        function toTC() {
            map.panTo(new google.maps.LatLng(24.18492,120.3545689));
            map.setZoom(9.61);
        }
        function toTE() {
            map.panTo(new google.maps.LatLng(23.9973188,121.638203));
            map.setZoom(9.3);
        }
        function toTS() {
            map.panTo(new google.maps.LatLng(22.7256725,120.4536938));
            map.setZoom(9.13);
        }
        function toThai() {
            map.panTo(new google.maps.LatLng(14.331616, 101.300531));
            map.setZoom(5);
        }
        function toAlaska() {
            map.panTo(new google.maps.LatLng(60.696753, -156.535547));
            map.setZoom(4);
        }
        </script>
        <?php
    }
}

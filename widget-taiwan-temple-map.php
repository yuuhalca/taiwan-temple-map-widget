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
        <style>
        #map-canvas .gm-svpc img {
            max-width: initial;
        }
        </style>
        <div id="map-canvas" style="width:100%;height:80vh;"></div>
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
        var mapStyles = [
            {
                "featureType": "all",
                "elementType": "all",
                "stylers": [
                    {
                        "color": "#ff7000"
                    },
                    {
                        "lightness": "69"
                    },
                    {
                        "saturation": "100"
                    },
                    {
                        "weight": "1.17"
                    },
                    {
                        "gamma": "2.04"
                    }
                ]
            },
            {
                "featureType": "all",
                "elementType": "geometry",
                "stylers": [
                    {
                        "color": "#cb8536"
                    }
                ]
            },
            {
                "featureType": "all",
                "elementType": "labels",
                "stylers": [
                    {
                        "color": "#ffb471"
                    },
                    {
                        "lightness": "66"
                    },
                    {
                        "saturation": "100"
                    }
                ]
            },
            {
                "featureType": "all",
                "elementType": "labels.text.fill",
                "stylers": [
                    {
                        "gamma": 0.01
                    },
                    {
                        "lightness": 20
                    }
                ]
            },
            {
                "featureType": "all",
                "elementType": "labels.text.stroke",
                "stylers": [
                    {
                        "saturation": -31
                    },
                    {
                        "lightness": -33
                    },
                    {
                        "weight": 2
                    },
                    {
                        "gamma": 0.8
                    }
                ]
            },
            {
                "featureType": "all",
                "elementType": "labels.icon",
                "stylers": [
                    {
                        "visibility": "off"
                    }
                ]
            },
            {
                "featureType": "landscape",
                "elementType": "all",
                "stylers": [
                    {
                        "lightness": "-8"
                    },
                    {
                        "gamma": "0.98"
                    },
                    {
                        "weight": "2.45"
                    },
                    {
                        "saturation": "26"
                    }
                ]
            },
            {
                "featureType": "landscape",
                "elementType": "geometry",
                "stylers": [
                    {
                        "lightness": 30
                    },
                    {
                        "saturation": 30
                    }
                ]
            },
            {
                "featureType": "poi",
                "elementType": "geometry",
                "stylers": [
                    {
                        "saturation": 20
                    }
                ]
            },
            {
                "featureType": "poi.park",
                "elementType": "geometry",
                "stylers": [
                    {
                        "lightness": 20
                    },
                    {
                        "saturation": -20
                    }
                ]
            },
            {
                "featureType": "road",
                "elementType": "geometry",
                "stylers": [
                    {
                        "lightness": 10
                    },
                    {
                        "saturation": -30
                    }
                ]
            },
            {
                "featureType": "road",
                "elementType": "geometry.stroke",
                "stylers": [
                    {
                        "saturation": 25
                    },
                    {
                        "lightness": 25
                    }
                ]
            },
            {
                "featureType": "water",
                "elementType": "all",
                "stylers": [
                    {
                        "lightness": -20
                    },
                    {
                        "color": "#ecc080"
                    }
                ]
            }
        ];
        var opts = {
            center: new google.maps.LatLng(23.823538, 121.030859),
            zoom: 7,
            styles: mapStyles,
            mapTypeControl: false, // 地図・航空写真ボタンを非表示
            streetViewControl: true // ペグマン（ストリートビュー）を必ず表示
        };
        var map = new google.maps.Map(mapDiv, opts);

        // カスタムコントロール（エリア拡大ボタン・最小化対応）
        var controlDiv = document.createElement('div');
        controlDiv.style.margin = '10px';
        controlDiv.style.background = 'rgba(255,255,255,0.95)';
        controlDiv.style.borderRadius = '8px';
        controlDiv.style.boxShadow = '0 2px 6px rgba(0,0,0,0.3)';
        controlDiv.style.padding = '8px 10px';
        controlDiv.style.display = 'flex';
        controlDiv.style.flexWrap = 'wrap';
        controlDiv.style.gap = '4px';
        controlDiv.style.alignItems = 'center';
        controlDiv.style.transition = 'width 0.3s, min-width 0.3s, padding 0.3s';
        controlDiv.id = 'custom-area-control';

        // 展開状態のHTML
        var expandedHTML = `
            <input type="button" value="台湾全体" onclick="toTaiwan()" />
            <input type="button" value="台湾北部" onclick="toTN()" />
            <input type="button" value="台湾中西部" onclick="toTC()" />
            <input type="button" value="台湾中東部" onclick="toTE()" />
            <input type="button" value="台湾南部" onclick="toTS()" />
            <input type="button" value="日本" onclick="toJapan()" />
            <input type="button" value="タイ" onclick="toThai()" />
            <input type="button" value="アラスカ" onclick="toAlaska()" />
            <span id="minimize-btn" style="cursor:pointer;font-size:18px;margin-left:8px;">◀</span>
        `;
        // 最小化状態のHTML
        var minimizedHTML = `<span id="expand-btn" style="cursor:pointer;font-size:18px;">▶</span>`;

        controlDiv.innerHTML = expandedHTML;
        map.controls[google.maps.ControlPosition.TOP_LEFT].push(controlDiv);

        // 最小化・展開の切り替え
        function minimizeControl() {
            controlDiv.innerHTML = minimizedHTML;
            controlDiv.style.minWidth = '32px';
            controlDiv.style.width = '32px';
            controlDiv.style.padding = '8px 6px';
            // ▶ボタンにイベントを付与
            var expandBtn = document.getElementById('expand-btn');
            if(expandBtn) expandBtn.onclick = function(e) {
                e.stopPropagation();
                expandControl();
            };
        }
        function expandControl() {
            controlDiv.innerHTML = expandedHTML;
            controlDiv.style.minWidth = '';
            controlDiv.style.width = '';
            controlDiv.style.padding = '8px 10px';
            setMinimizeEvent();
        }
        function setMinimizeEvent() {
            var minBtn = document.getElementById('minimize-btn');
            if(minBtn) minBtn.onclick = function(e) {
                e.stopPropagation();
                minimizeControl();
            };
        }
        // 最初は展開、3秒後に自動で最小化
        setTimeout(minimizeControl, 3000);
        setMinimizeEvent();
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

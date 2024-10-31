<?php
/*
Plugin Name: New Nepali Calendar
Plugin URI:
Description: Add Nepali Calendar to your blog.
Version: 1.1.0
Author: Blogger Nepal
Author URI: https://www.bloggernepal.com/
License: TCIY
*/
// The widget class

class NewNepaliCalendar extends WP_Widget {
	public function __construct() {
		parent::__construct(
			'NewNepaliCalendar',
			__( 'Add New Nepali Calendar', 'text_domain' ),
			array(
				'customize_selective_refresh' => true,
			)
		);
	}
	public function form( $instance ) {
		$defaults = array(
			'title'    => 'Nepali Calendar',
		);

		extract( wp_parse_args( ( array ) $instance, $defaults ) ); ?>

		<?php // Widget Title ?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Widget Title', 'text_domain' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>

	<?php }
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title']    = isset( $new_instance['title'] ) ? wp_strip_all_tags( $new_instance['title'] ) : '';
		return $instance;
	}
	public function widget( $args, $instance ) {
		extract( $args );
		$title    = isset( $instance['title'] ) ? apply_filters( 'widget_title', $instance['title'] ) : '';
		// WordPress core before_widget hook (always include )
		$apiResponse = wp_remote_get( 'https://calendar.bloggernepal.com/api/today/' );
		$body = json_decode( wp_remote_retrieve_body( $apiResponse ), true );

		$message = $body["message"];
		$res = $body["res"];

		$month = $res["name"];
		$year = $res["year"];
		$engMonth1 = $res["eng1"];
		$engMonth2 = $res["eng2"];
		$engYear = $res["engYear"];

		$days = $res["days"];
		echo '
			'. $before_widget;
			// Display the widget
			echo '
				<div>
				<style>
					.npCalendarTable{
					       width: 100%;
					       text-align: center;
					}
					.npCalendarTable td,th{
					       font-size: 1.3em;
					       text-align: center;
					       padding:0 !important;
					}
					.npCalendarPreNext{
					       color: #9e9e9e;
					}
					.npCalendarToday{
					       background: #428bca;
					}
				</style>
				';
					if ( $title ) {
						echo '	'.$before_title . $title . $after_title;
					}
					echo'
					<table class="npCalendarTable">
				                <thead>
							<tr>
								<th colspan="7">'.$month.' '.$year.'</th>
				                        <tr>
				                                <th title="आइतवार">आइत</th>
				                                <th title="सोमवार">सोम</th>
				                                <th title="मगलवार">मगल</th>
				                                <th title="बुधवार">बुध</th>
				                                <th title="बिहिवार">बिहि</th>
				                                <th title="शुक्रवार">शुक्र</th>
				                                <th title="शनिवार">शनि</th>
				                        </tr>
				                </thead>
				                <tbody>';
						for($i = 0; $i< 42; $i++){
							if($i == 0 || $i == 7 || $i == 14 || $i == 21 || $i == 28 || $i == 35){
								echo'<tr>';
							}
							$thisday = $days[$i];
							$thisbs = $thisday["bs"];
							$thistag = ''.$thisday["tag"];
							if($thistag == "pre" || $thistag == "next"){
								echo '<td class="npCalendarPreNext">';
							} else if($thistag == "today"){
								echo '<td class="npCalendarToday">';
							} else{
								echo '<td>';
							}
							echo $thisbs;
							echo '</td>';
							if($i == 6 || $i == 13 || $i == 20 || $i == 27 || $i == 34 || $i == 41){
								echo'</tr>';
							}
						}
						echo '
				                </tbody>
				        </table>
	';
				echo '
				</div>';
			// WordPress core after_widget hook (always include )
			echo '
			' . $after_widget . '
	';
	}
}

function HTMLJS(){
		$html =  <<<EOT
<div class="widget-content">
        <div>
                <style>
                        .npCalendarTable {
                                width: 100%;
                                text-align: center;
                        }

                        .npCalendarTable td {
                                font-size: 1.5em;
                                text-align: center;
                                padding: 0 !important;
                        }

                        .npCalendarTable tr {
                                text-align: center;
                                padding: 0 !important;
                        }

                        .npCalendarPreNext {
                                color: #9e9e9e;
                        }

                        .npCalendarToday {
                                background: #428bca;
                        }
                </style>
                <table class="npCalendarTable">
                        <thead>
                        		<tr>
                        			<th colspan="7" id="npMonthsYear">Nepali Calendar</th>
                        			
                        		</tr>
                                <tr>
                                        <th title="आइतवार">आइत</th>
                                        <th title="सोमवार">सोम</th>
                                        <th title="मगलवार">मगल</th>
                                        <th title="बुधवार">बुध</th>
                                        <th title="बिहिवार">बिहि</th>
                                        <th title="शुक्रवार">शुक्र</th>
                                        <th title="शनिवार">शनि</th>
                                </tr>
                        </thead>
                        <tbody id="npCalendarTBody">

                        </tbody>
                </table>
        </div>
        <script>
                let getJSON = function(url, callback) {
                        let xmlhttp = new XMLHttpRequest();
                        xmlhttp.open('GET', url, true);
                        xmlhttp.responseType = 'json';
                        xmlhttp.onload = function() {
                                let status = xmlhttp.status;
                                if (status === 200) {
                                        callback(null, xmlhttp.response);
                                } else {
                                        callback(status, xmlhttp.response);
                                }
                        };
                        xmlhttp.send();
                };

                let fetchJsonData = (err, jsonData) => {
                        const res = jsonData.res;
                        const year = res.year;
                        const month = res.name;
                        const days = res.days;
                        document.getElementById("npMonthsYear").innerText = month + " " + year;
                        var html = '';
                        for (var i = 0; i < 42; i++) {
                                if (i == 0 || i == 7 || i == 14 || i == 21 || i == 28 || i == 35) {
                                        html = html + "<tr>"
                                }
                                var thisday = days[i];
                                var thisbs = thisday.bs;
                                var thistag = thisday.tag;
                                if (thistag == "pre" || thistag == "next") {
                                        html = html + '<td class="npCalendarPreNext">';
                                } else if (thistag == "today") {
                                        html = html + '<td class="npCalendarToday">';
                                } else {
                                        html = html + '<td>';
                                }
                                html = html + thisbs;
                                html = html + '</td>'

                                if (i == 6 || i == 13 || i == 20 || i == 27 || i == 34 || i == 41) {
                                        html = html + "</tr>"
                                }
                        }
                        document.getElementById('npCalendarTBody').innerHTML = html;
                }
                //getJSON('http://localhost:4040/api/today',fetchJsonData);
                getJSON('https://calendar.bloggernepal.com/api/today/', fetchJsonData);
        </script>
</div>
EOT;
		return $html;
}

function register_NewNepaliCalendar() {
	register_widget( 'NewNepaliCalendar' );
}
add_action( 'widgets_init', 'register_NewNepaliCalendar' );
add_shortcode('NepaliCalendar', 'HTMLJS');
add_shortcode('NewNepaliCalendar', 'HTMLJS');
?>

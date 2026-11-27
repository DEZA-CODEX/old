<?php
namespace LoftOcean\Utils;
if ( ! class_exists( '\LoftOcean\Utils\Rooms' ) ) {
    class Rooms {
        /**
        * Pagination type
        */
        protected $pagination = false;
        /*
        * Room Cache
        */
        protected $rooms_cache = array();
        /**
        * URL params
        */
        protected $url_params = null;
        /**
        * Booking form url params
        */
        protected $booking_form_url_params = null;
        /*
        * Construct function
        */
        public function __construct() {
            add_action( 'loftocean_rooms_widget_the_list_content', array( $this, 'the_room_list' ), 10, 2 );
            add_action( 'loftocean_the_room_facilities', array( $this, 'the_room_facilities' ), 10, 4 );
            add_action( 'loftocean_ajax_room_load_more', array( $this, 'rooms_load_more' ), 10, 2 );
            add_action( 'pre_get_posts', array( $this, 'room_search_args' ), 9999999 );
            add_action( 'loftocean_before_room_search_list', array( $this, 'room_search_result_page_before_list' ) );
            add_action( 'loftocean_after_room_search_list', array( $this, 'room_search_result_page_after_list' ) );

            add_filter( 'loftocean_get_room_details', array( $this, 'get_room_details' ), 10, 2 );
            add_filter( 'loftocean_room_top_section', array( $this, 'get_single_room_top_section' ), 999 );
            add_filter( 'loftocean_room_search_vars', array( $this, 'get_room_search_vars' ) );
            add_filter( 'loftocean_room_booking_url_param', array( $this, 'get_room_booking_url_param' ) );
            add_filter( 'loftocean_get_search_var_person', array( $this, 'get_check_in_person' ) );
            add_filter( 'woocommerce_cart_item_permalink', array( $this, 'change_product_link' ), 9999, 3 );
            add_filter( 'loftocean_simiar_rooms_section_title', array( $this, 'simiar_rooms_section_default_title' ) );
        }
        /**
        * Ajax request handler
        */
        public function rooms_load_more( $query, $settings ) {
            query_posts( apply_filters( 'loftocean_ajax_rooms_query_args', $query, $settings ) );
            $template = $this->get_room_template_file( $settings[ 'layout' ] );
            global $wp_query;
            $results = array();

            if ( have_posts() && file_exists( $template ) ) {
                $paged = max( 1, intval( $query[ 'paged' ] ) );
                $sets = array( 'args' => $settings );
                add_filter( 'excerpt_length', array( $this, 'room_list_excerpt_length' ), PHP_INT_MAX );
                add_filter( 'excerpt_more', array( $this, 'room_list_excerpt_more' ), PHP_INT_MAX );
                while( have_posts() ) {
                    the_post();
                    ob_start();
                    require $template;
                    $results[] = ob_get_clean();
                }
                wp_reset_postdata();
                remove_filter( 'excerpt_length', array( $this, 'room_list_excerpt_length' ), PHP_INT_MAX );
                remove_filter( 'excerpt_more', array( $this, 'room_list_excerpt_more' ), PHP_INT_MAX );
                $more = ( $paged < $wp_query->max_num_pages );
                wp_reset_query();
                wp_send_json_success( array(
                    'more' => $more,
                    'items' => $results,
                    'nav' => '',
                ) );
            } else {
                wp_send_json_success( array(
                    'more' => false,
                    'nav' => '',
                    'items' => []
                ) );
            }
            wp_send_json_error();
        }
        /**
        * Add classname post to class list
        */
        public function add_post_class( $class ) {
            array_push( $class, 'post', 'cs-room-item' );
            return $class;
        }
        /**
        * Room search result page query args
        */
        public function room_search_args( $query ) {
            if ( $query->is_main_query() && ( ! is_admin() ) && isset( $_GET[ 'search_rooms' ] ) ) {
                $page = get_post( $query->get( 'page_id' ) );
                if ( ! ( empty( $page ) || is_wp_error( $page ) ) ) {
                    global $wp_post_types;
                    $query->set( 'page_id', '' );
                    $wp_post_types['loftocean_room']->ID = $page->ID;
                    $wp_post_types['loftocean_room']->post_name	= $page->post_name;
                    $wp_post_types['loftocean_room']->post_type = $page->post_type;
                    $wp_post_types['loftocean_room']->ancestors = get_ancestors( $page->ID, $page->post_type );
                    $wp_post_types['loftocean_room']->post_title = $page->post_title;

                    $query->is_page = true;
                    $query->is_singular = false;
                    $query->is_post_type_archive = true;
                    $query->is_archive = true;
                }

                $params = array( 'checkin', 'checkout', 'room-quantity' );
                $GETData = apply_filters( 'loftocean_room_search_vars', array() );
                $data = array();
                if ( \LoftOcean\is_valid_array( $GETData ) ) {
                    foreach ( $params as $param ) {
                        if ( isset( $GETData[ $param ] ) ) {
                            $val = $GETData[ $param ];
                            if ( in_array( $param, array( 'checkin', 'checkout' ) ) ) {
                                $val = strtotime( $val );
                            }
                            $val = absint( $val );
                            if ( $val > 0 ) {
                                $data[ $param ] = $val;
                            }
                        }
                    }
                }
                $default_data = array( 'checkin' => strtotime( date( 'Y-m-d' ) ), 'room-quantity' => 1 );
    			$default_data[ 'checkout' ] = $default_data[ 'checkin' ] + LOFTICEAN_SECONDS_IN_DAY;
    			$data = \LoftOcean\is_valid_array( $data ) ? array_merge( $default_data, $data ) : $default_data;

                if ( $data[ 'checkout' ] <= $data[ 'checkin' ] ) {
                    $query->set( 'post_type', 'loftocean_room_not_existing' );
                    return false;
                }

                $excluded_room_IDs = apply_filters( 'loftocean_get_unavailable_rooms', array(), $data );
                if ( \LoftOcean\is_valid_array( $excluded_room_IDs ) ) {
                    $query->set( 'post__not_in', $excluded_room_IDs );
                }
                $query->set( 'posts_per_page', apply_filters( 'loftocean_rooms_search_page_ppp', get_option( 'posts_per_page', 10 ) ) );
                $query->set( 'post_type', 'loftocean_room' );
                if ( isset( $query->query['paged'] ) ) {
					$query->set( 'paged', $query->query['paged'] );
				}

                $person_sum = apply_filters( 'loftocean_get_search_var_person', 0 );
                $person_sum = absint( $person_sum );
                if ( ! empty( $person_sum ) ) {
                    $query->set( 'meta_query', array( 'relation' => 'AND', array(
                        'relation' => 'OR',
                        array(
                            'key' => 'loftocean_room_max_people',
                            'value' => array( '' ),
                            'compare' => 'IN'
                        ),
                        array(
                            'key' => 'loftocean_room_max_people',
                            'compare' => 'NOT EXISTS'
                        ),
                        array(
                            'key' => 'loftocean_room_max_people',
                            'value' => $person_sum,
                            'compare' => '>=',
                            'type' => 'NUMERIC'
                        )
                    ), array(
                        'relation' => 'OR',
                        array(
                            'key' => 'loftocean_room_min_people',
                            'value' => array( '' ),
                            'compare' => 'IN'
                        ),
                        array(
                            'key' => 'loftocean_room_min_people',
                            'compare' => 'NOT EXISTS'
                        ),
                        array(
                            'key' => 'loftocean_room_min_people',
                            'value' => $person_sum,
                            'compare' => '<=',
                            'type' => 'NUMERIC'
                        )
                    ) ) );
                }
                do_action( 'loftocean_room_search_pre_get_rooms', $query, $data );
                do_action_ref_array( 'loftocean_room_search_booking_rules', array( &$query, array_merge( array( 'guests' => $person_sum ), $data ) ) );
            }
        }
        /**
        * Get room search vars
        */
        public function get_room_search_vars( $vars = array() ) {
            if ( is_null( $this->url_params ) && isset( $_GET[ 'roomSearchNonce' ] ) ) {
                $this->url_params = array();
                $data = base64_decode( wp_unslash( $_GET[ 'roomSearchNonce' ] ) );
                $data = json_decode( $data, true );
                if ( \LoftOcean\is_valid_array( $data ) ) {
                    foreach ( $data as $d ) {
                        $this->url_params[ $d[ 'name' ] ] = $d[ 'value' ];
                    }
                }
            }
            if ( \LoftOcean\is_valid_array( $this->url_params ) ) {
                $params = array( 'checkin', 'checkout', 'room-quantity', 'adult-quantity', 'child-quantity' );
                foreach( $params as $param ) {
                    if ( isset( $this->url_params[ $param ] ) ) {
                        $vars[ $param ] = $this->url_params[ $param ];
                    }
                }
            }
            return $vars;
        }
        /**
        * Get person count need to check in
        */
        public function get_check_in_person( $count = 0 ) {
            $vars = apply_filters( 'loftocean_room_search_vars', array() );
            $sum = 0;
            if ( ( ! empty( $vars[ 'children' ] ) ) || ( ! empty( $vars[ 'adults' ] ) ) ) {
                $sum += empty( $vars[ 'children' ] ) ? 0 : $vars[ 'children' ];
                $sum += empty( $vars[ 'adults' ] ) ? 0 : $vars[ 'adults' ];
            }
            if ( empty( $sum ) && ( ( ! empty( $vars[ 'adult-quantity' ] ) ) || ( ! empty( $vars[ 'child-quantity' ] ) ) ) ) {
                $sum += empty( $vars[ 'adult-quantity' ] ) ? 0 : $vars[ 'adult-quantity' ];
                $sum += empty( $vars[ 'child-quantity' ] ) ? 0 : $vars[ 'child-quantity' ];
            }
            return $sum;
        }
        /*
        * Room List
        */
        public function the_room_list( $sets, $no_pagination = true ) {
            if ( have_posts() ) :
                $template = $this->get_room_template_file( $sets[ 'args' ][ 'layout' ] );
                if ( file_exists( $template ) ) :
                    $class = apply_filters( 'loftocean_rooms_list_wrapper_class', $sets[ 'wrap_class' ], $sets[ 'args' ] ); ?>
                    <div class="<?php echo esc_attr( implode( ' ', $class ) ); ?>"<?php do_action( 'loftocean_rooms_wrap_attributes' ); ?>>
                        <div class="posts-wrapper cs-rooms-wrapper"><?php
                        add_filter( 'excerpt_length', array( $this, 'room_list_excerpt_length' ), PHP_INT_MAX );
                        add_filter( 'excerpt_more', array( $this, 'room_list_excerpt_more' ), PHP_INT_MAX );
                        global $wp_query;
                        while( have_posts() ) {
                            the_post();
                            $sets[ 'current_index' ] = $wp_query->current_post + 1;
                            require $template;
                        }
                        wp_reset_postdata();
                        remove_filter( 'excerpt_length', array( $this, 'room_list_excerpt_length' ), PHP_INT_MAX );
                        remove_filter( 'excerpt_more', array( $this, 'room_list_excerpt_more' ), PHP_INT_MAX ); ?>
                        </div><?php
                        $no_pagination ? '' : $this->the_pagination( $sets ); ?>
                    </div><?php
                endif;
            endif;
        }
        /**
        * Room list excerpt length
        */
        public function room_list_excerpt_length( $length ) {
            return 25;
        }
        /**
        * Room list excerpt more
        */
        public function room_list_excerpt_more( $more ) {
        	return ' ...';
        }
        /**
        * Show pagination
        */
        protected function the_pagination( $sets ) {
            $type = isset( $sets, $sets[ 'pagination' ] ) ? $sets[ 'pagination' ] : '';
            global $wp_query, $paged;
            $current_page = max( $paged, 1 );

            if ( ( ! empty( $type ) ) &&
                ( ( ( $current_page < $wp_query->max_num_pages ) && in_array( $type, array( 'ajax-manual', 'ajax-auto' ) ) )
                    || ( in_array( $type, array( 'link-number', 'link-only' ) ) && ( $wp_query->max_num_pages > 1 ) ) ) ) {
                $template = LOFTOCEAN_DIR . 'template-parts/pagination/pagination-' . $type . '.php';
                if ( file_exists( $template ) ) {
                    require $template;
                }
            }
        }
        /**
        * Change product link
        */
        public function change_product_link( $link, $item, $cart_item_key ) {
            if ( \LoftOcean\is_valid_array( $item ) && isset( $item[ 'loftocean_booking_data' ], $item[ 'loftocean_booking_data' ][ 'room_id' ] ) ) {
                return get_the_permalink( $item[ 'loftocean_booking_data' ][ 'room_id' ] );
            }
            return $link;
        }
        /*
        * Get room settings
        */
        public function get_room_details( $details, $room_id ) {
            if ( 'loftocean_room' == get_post_type( $room_id ) ) {
                $room_key = 'room-' . $room_id;
                if ( ! isset( $this->rooms_cache[ $room_key ] ) ) {
                    $list_thumbnail = get_post_meta( $room_id, 'loftocean_room_list_thumbnail_id', true );
                    $gallery = get_post_meta( $room_id, 'loftocean_room_gallery_ids', true );

                    $this->rooms_cache[ $room_key ] = apply_filters( 'loftocean_room_details', array(
                        'featuredImage' => has_post_thumbnail( $room_id ) ? get_post_thumbnail_id() : false,
                        'listImage' => empty( $list_thumbnail ) || ( ! \LoftOcean\media_exists( $list_thumbnail ) ) ? false : $list_thumbnail,
                        'gallery' => empty( $gallery ) ? false : explode( ',', $gallery ),
                        'roomSettings' => array(
                            'roomSubtitle' => get_post_meta( $room_id, 'loftocean_room_subtitle', true ),
                            'roomLabel' => get_post_meta( $room_id, 'loftocean_room_label', true ),
                            'roomNumber' => get_post_meta( $room_id, 'loftocean_room_number', true ),
                            'topSection' => get_post_meta( $room_id, 'loftocean_room_top_section', true ),
                            'bookingForm' => get_post_meta( $room_id, 'loftocean_room_booking_form', true ),
                			'regularPrice' => get_post_meta( $room_id, 'loftocean_room_regular_price', true ),
            				'minPeople' => get_post_meta( $room_id, 'loftocean_room_min_people', true ),
            				'maxPeople' => get_post_meta( $room_id, 'loftocean_room_max_people', true ),
            				'priceByPeople' => get_post_meta( $room_id, 'loftocean_room_price_by_people', true ),
            				'pricePerAdult' => get_post_meta( $room_id, 'loftocean_room_price_per_adult', true ),
            				'pricePerChild' => get_post_meta( $room_id, 'loftocean_room_price_per_child', true ),
                            'enableWeekendPrices' => get_post_meta( $room_id, 'loftocean_room_enable_weekend_prices', true ),
                            'weekendPricePerNight' => get_post_meta( $room_id, 'loftocean_room_weekend_price_per_night', true ),
                            'weekendPricePerAdult' => get_post_meta( $room_id, 'loftocean_room_weekend_price_per_adult', true ),
                            'weekendPricePerChild' => get_post_meta( $room_id, 'loftocean_room_weekend_price_per_child', true )
                        )
                    ) );;
                }
                return $this->rooms_cache[ $room_key ];
            }
            return false;
        }
        /*
        * Get single room top section
        */
        public function get_single_room_top_section( $top_section = '' ) {
            $details = apply_filters( 'loftocean_get_room_details', array(), get_queried_object_id() );
            if ( isset( $details, $details[ 'roomSettings' ], $details[ 'roomSettings' ][ 'topSection' ] ) ) {
                return empty( $details[ 'roomSettings' ][ 'topSection' ] ) ? $top_section : $details[ 'roomSettings' ][ 'topSection' ];
            }
            return $top_section;
        }
        /*
        * Facility list
        */
        public function the_room_facilities( $room_id, $col = '', $style = '', $limit = '' ) {
            $limit = empty( $limit ) ? ( ( $col == '3' ) ? 3 : ( ( '2' == $col ) ? 4 : 9999 ) ) : $limit;
            $facilities = wp_get_post_terms( $room_id, 'lo_room_facilities', array(
                'orderby' => 'meta_value_num',
                'order' => 'ASC',
                'meta_key' => 'priority'
            ) );
            if ( is_wp_error( $facilities ) || empty( $facilities ) ) return ; ?>
            <div class="cs-room-basic-info">
                <ul><?php
                foreach ( $facilities as $index => $facility ) :
                    if ( $index >= $limit ) break;

                    $facilityID = $facility->term_id;
                    $facility_type = get_term_meta( $facilityID, 'facility_type', true );
                    $icon = get_term_meta( $facilityID, 'icon', true );
                    $label = '';
                    switch ( $facility_type ) {
                        case 'room-footage':
                            $icon = empty( $icon ) ? '' : '<i class="flaticon flaticon-' . $icon . '"></i>';
                            $number = get_post_meta( $room_id, 'loftocean_room_facility_room_footage_number', true );
                            $unit = get_post_meta( $room_id, 'loftocean_room_facility_room_footage_unit', true );
                            $label = empty( $number ) ? '' : sprintf( '%1$s %2$s<sup>2</sup>', $number, ( 'sf' == $unit ? 'ft' : 'm' ) );
                            break;
                        case 'guests':
                            $icon = empty( $icon ) ? '' : '<i class="flaticon flaticon-' . $icon . '"></i>';
                            $number = get_post_meta( $room_id, 'loftocean_room_facility_guests_number', true );
                            $unit = get_post_meta( $room_id, 'loftocean_room_facility_guests_label', true );
                            $label = empty( $number ) ? '' : sprintf( '%1$s %2$s', $number, $unit );
                            break;
                        case 'beds':
                            $icon = empty( $icon ) ? '' : '<i class="flaticon flaticon-' . $icon . '"></i>';
                            $number = get_post_meta( $room_id, 'loftocean_room_facility_beds_number', true );
                            $unit = get_post_meta( $room_id, 'loftocean_room_facility_beds_label', true );
                            $label = empty( $number ) ? '' : sprintf( '%1$s %2$s', $number, $unit );
                            break;
                        case 'bathrooms':
                            $icon = empty( $icon ) ? '' : '<i class="flaticon flaticon-' . $icon . '"></i>';
                            $number = get_post_meta( $room_id, 'loftocean_room_facility_bathrooms_number', true );
                            $unit = get_post_meta( $room_id, 'loftocean_room_facility_bathrooms_label', true );
                            $label = empty( $number ) ? '' : sprintf( '%1$s %2$s', $number, $unit );
                            break;
                        case 'free-wifi':
                            $icon = empty( $icon ) ? '' : '<i class="flaticon flaticon-' . $icon . '"></i>';
                            $label = $facility->name;
                            $custom_label = get_post_meta( $room_id, 'loftocean_room_facility_free_wifi_label', true );
                            $label = empty( $custom_label ) ? $label : $custom_label;
                            break;
                        case 'air-conditioning':
                            $icon = empty( $icon ) ? '' : '<i class="flaticon flaticon-' . $icon . '"></i>';
                            $label = $facility->name;
                            $custom_label = get_post_meta( $room_id, 'loftocean_room_facility_air_conditioning_label', true );
                            $label = empty( $custom_label ) ? $label : $custom_label;
                            break;
                        case 'custom-facility':
                            $icon = empty( $icon ) ? '' : '<i class="flaticon flaticon-' . $icon . '"></i>';
                            $label = $facility->name;
                            $custom_label = get_post_meta( $room_id, 'loftocean_room_facility_custom_label_' . $facilityID, true );
                            $label = empty( $custom_label ) ? $label : $custom_label;
                            break;
                    }
                    $has_icon = ! empty( $icon );
                    $has_label = ! empty( $label );
                    if ( $has_icon || $has_label ) : ?>
                        <li>
                            <?php if ( $has_icon ) : ?><div class="csrbi-icon"><?php echo $icon; ?></div><?php endif; ?>
                            <?php if ( $has_label ) : ?><span class="csrbi-text"><?php echo $label; ?></span><?php endif; ?>
                        </li><?php
                    endif;
                endforeach; ?>
                </ul>
            </div><?php
        }
        /**
        * Get room template file
        */
        protected function get_room_template_file( $layout ) {
            return LOFTOCEAN_DIR . 'template-parts/room-content-' . ( ( ! empty( 'layout' ) ) && in_array( $layout, array( 'overlay', 'coverlay', 'coverlays' ) ) ? 'overlay' : 'normal' ) . '.php';
        }
        /**
        * Actions before room search result page list content
        */
        public function room_search_result_page_before_list() {
            add_action( 'the_permalink', array( $this, 'single_room_permalink_args' ), 99, 2 );
        }
        /**
        * Actions after room search result page list content
        */
        public function room_search_result_page_after_list() {
            remove_action( 'the_permalink', array( $this, 'single_room_permalink_args' ), 99, 2 );
        }
        /**
        * Add URL args to single room url
        */
        public function single_room_permalink_args( $link, $post ) {
            if ( apply_filters( 'loftocean_pass_params_from_search_result', false ) ) {
                $vars = apply_filters( 'loftocean_room_search_vars', array() );

                $default_vars = array( 'checkin' => date( 'Y-m-d' ), 'room-quantity' => 1, 'adult-quantity' => 0, 'child-quantity' => 0 );
                $default_vars[ 'checkout' ] = date( 'Y-m-d', strtotime( 'tomorrow' ) );

                $args = array();
                $params = array( 'checkin' => 'checkin', 'checkout' => 'checkout', 'room-quantity' => 'room', 'adult-quantity' => 'adult', 'child-quantity' => 'child' );
                foreach ( $params as $param => $args_param ) {
                    $args[ $args_param ] = isset( $vars[ $param ] ) ? $vars[ $param ] : $default_vars[ $param ];
                }
                if ( \LoftOcean\is_valid_array( $args ) ) {
                    $args = array( 'booking-data' => base64_encode( json_encode( $args ) ) );
                }

                return add_query_arg( array( $args ), $link );
            }
            return $link;
        }
        /**
        * Get URL params for room booking form
        */
        public function get_room_booking_url_param( $params ) {
            if ( is_null( $this->booking_form_url_params ) ) {
                $this->booking_form_url_params = array();
                if ( isset( $_GET[ 'booking-data' ] ) ) {
                    $data = base64_decode( wp_unslash( $_GET[ 'booking-data' ] ) );
                    $data = json_decode( $data, true );
                    if ( \LoftOcean\is_valid_array( $data ) ) {
                        foreach ( $data as $name => $val ) {
                            $this->booking_form_url_params[ $name ] = $val;
                        }
                    }
                }
            }
            return $this->booking_form_url_params;
        }
        /**
        * Similar rooms section default title
        */
        public function simiar_rooms_section_default_title( $title ) {
            return ( false === $title ) ? esc_html__( 'Similar Rooms', 'loftocean' ) : $title;
        }
    }
    new Rooms();
}

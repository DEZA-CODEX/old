<?php
/**
* Template for rooms search result page
*/
$room_search_vars = apply_filters( 'loftocean_room_search_vars', array() );
$hide_fields = apply_filters( 'loftocean_room_reservation_form_hide_fields', array() );
$hide_room = isset( $hide_fields, $hide_fields[ 'room' ] ) && ( ! empty( $hide_fields[ 'room' ] ) );
$hide_adult = isset( $hide_fields, $hide_fields[ 'adult' ] ) && ( ! empty( $hide_fields[ 'adult' ] ) );
$hide_children = isset( $hide_fields, $hide_fields[ 'child' ] ) && ( ! empty( $hide_fields[ 'child' ] ) );
$display_date_format = apply_filters( 'loftocean_display_date_format', 'YYYY-MM-DD' );
$date_format = 'YYYY-MM-DD';
get_header();
get_template_part( 'template-parts/page-header/room-search' ); ?>

<div class="main">
    <div class="container">
        <div id="primary" class="primary content-area"><?php
        if ( have_posts() ) :
            do_action( 'loftocean_before_room_search_list' );
            do_action( 'loftocean_rooms_widget_the_list_content', array(
                'args' => array( 'layout' => 'list', 'columns' => '', 'metas' => array( 'excerpt', 'read_more_btn', 'subtitle', 'label' ), 'page_layout' => '' ),
                'wrap_class' => array( 'posts', 'cs-rooms', 'layout-list', 'img-ratio-3-2' ),
                'pagination' => 'link-number'
            ), false );
            do_action( 'loftocean_after_room_search_list' );
        else : ?>
            <div class="no-room-found">
                <p class="no-room-found-error-message"><?php esc_html_e( 'Sorry, we currently don\'t have any rooms that match your search. Please try changing the search parameters and searching again.', 'loftocean' ); ?></p>
            </div><?php
        endif;
        wp_reset_query(); ?>
        </div>

        <aside id="secondary" class="sidebar">
            <div class="sidebar-container">
                <div class="cs-reservation-form style-block cs-form-square inline-label"><?php
                $search_url = apply_filters( 'loftocean_search_url', home_url( '/' ) ); ?>
                    <form class="cs-form-wrap" data-display-date-format="<?php echo esc_attr( $display_date_format ); ?>" data-date-format="<?php echo esc_attr( $date_format ); ?>" action="<?php echo esc_url( $search_url ); ?>" method="GET"><?php
                        $checkin_date = isset( $room_search_vars[ 'checkin' ] ) ? $room_search_vars[ 'checkin' ] : date( esc_html__( 'Y-m-d', 'loftocean' ) );
                        $checkout_date = isset( $room_search_vars[ 'checkout' ] ) ? $room_search_vars[ 'checkout' ] : date( esc_html__( 'Y-m-d', 'loftocean' ), strtotime( 'tomorrow' ) );
                        do_action( 'loftocean_room_search_form_fields_before' ); ?>
                        <div class="cs-form-field cs-check-in">
                            <div class="field-wrap">
                                <label class="cs-form-label"><?php esc_html_e( 'Check In', 'loftocean' ); ?></label>

                                <div class="field-input-wrap checkin-date">
                                    <input type="text" class="date-range-picker" value="<?php echo $checkin_date; ?> - <?php echo $checkout_date; ?>">
                                    <input type="text" value="" data-value="<?php echo $checkin_date; ?>" class="check-in-date" name="" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="cs-form-field cs-check-out">
                            <div class="field-wrap">
                                <label class="cs-form-label"><?php esc_html_e( 'Check Out', 'loftocean' ); ?></label>

                                <div class="field-input-wrap checkout-date">
                                    <input type="text" value="" data-value="<?php echo $checkout_date; ?>" name="" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="cs-form-field cs-rooms cs-has-dropdown<?php if ( $hide_room ) : ?> hide<?php endif; ?>">
                            <div class="field-wrap">
                                <label class="cs-form-label"><?php esc_html_e( 'Rooms', 'loftocean' ); ?></label><?php
                                $room_number = 1;
                                $room_label = esc_html__( '1 Room', 'loftocean' );
                                if ( isset( $room_search_vars[ 'room-quantity' ] ) && is_numeric( $room_search_vars[ 'room-quantity' ] ) ) {
                                    $room_number = $room_search_vars[ 'room-quantity' ];
                                    $room_label = $room_number . ' ' . ( $room_number > 1 ? esc_html__( 'Rooms', 'loftocean' ) : esc_html__( 'Room', 'loftocean' ) );
                                } ?>
                                <div class="field-input-wrap has-dropdown">
                                    <input type="text" name="" value="<?php echo esc_attr( $room_label ); ?>" readonly="">
                                </div>
                                <div class="csf-dropdown">
                                    <div class="csf-dropdown-item">
                                        <label class="cs-form-label"><?php esc_html_e( 'Rooms', 'loftocean' ); ?></label>
                                        <div class="quantity cs-quantity" data-label="room">
                                            <label class="screen-reader-text"><?php esc_html_e( 'Rooms quantity', 'loftocean' ); ?></label>
                                            <button class="minus"></button>
                                            <input type="text" name="room-quantity" value="<?php echo esc_attr( $room_number ); ?>" class="input-text" autocomplete="off" readonly="" data-min="1" data-max="50">
                                            <button class="plus"></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="cs-form-field cs-guests cs-has-dropdown<?php if ( $hide_adult && $hide_children ) : ?> hide<?php endif; ?>">
                            <div class="field-wrap">
                                <label class="cs-form-label"><?php esc_html_e( 'Guests', 'loftocean' ); ?></label><?php
                                $guest_label = array();
                                if ( $hide_adult && $hide_children ) {
                                    $room_search_vars[ 'adult-quantity' ] = 0;
                                    $room_search_vars[ 'child-quantity' ] = 0;
                                    array_push( $guest_label, esc_html__( '0 Adult', 'loftocean' ) );
                                } else {
                                    $room_search_vars[ 'adult-quantity' ] = isset( $room_search_vars[ 'adult-quantity' ] ) && is_numeric( $room_search_vars[ 'adult-quantity' ] ) ? $room_search_vars[ 'adult-quantity' ] : 0;
                                    $room_search_vars[ 'child-quantity' ] = isset( $room_search_vars[ 'child-quantity' ] ) && is_numeric( $room_search_vars[ 'child-quantity' ] ) ? $room_search_vars[ 'child-quantity' ] : 0;
                                    if ( $hide_adult ) {
                                        $room_search_vars[ 'adult-quantity' ] = 0;
                                    } else {
                                        $adult_label = $room_search_vars[ 'adult-quantity' ] . ' ' . ( $room_search_vars[ 'adult-quantity' ] > 1 ? esc_html__( 'Adults', 'loftocean' ) : esc_html__( 'Adult', 'loftocean' ) );
                                        if ( $hide_children ) {
                                            array_push( $guest_label, $adult_label );
                                        } else if ( $room_search_vars[ 'adult-quantity' ] > 0 ) {
                                            array_push( $guest_label, $adult_label );
                                        }
                                    }
                                    if ( $hide_children ) {
                                        $room_search_vars[ 'child-quantity' ] = 0;
                                    } else {
                                        $child_label = $room_search_vars[ 'child-quantity' ] . ' ' . ( $room_search_vars[ 'child-quantity' ] > 1 ? esc_html__( 'Children', 'loftocean' ) : esc_html__( 'Child', 'loftocean' ) );
                                        if ( $hide_adult ) {
                                            array_push( $guest_label, $child_label );
                                        } else if ( $room_search_vars[ 'child-quantity' ] > 0 ) {
                                            array_push( $guest_label, $child_label );
                                        }
                                    }
                                } ?>
                                <div class="field-input-wrap has-dropdown">
                                    <input type="text" name="" value="<?php echo implode( ', ', $guest_label ); ?>" readonly="">
                                </div>

                                <div class="csf-dropdown">
                                    <div class="csf-dropdown-item<?php if ( $hide_adult ) : ?> hide<?php endif; ?>">
                                        <label class="cs-form-label"><?php esc_html_e( 'Adults', 'loftocean' ); ?></label>

                                        <div class="quantity cs-quantity" data-label="adult">
                                            <label class="screen-reader-text"><?php esc_html_e( 'Adults quantity', 'loftocean' ); ?></label>
                                            <button class="minus"></button>
                                            <input type="text" name="adult-quantity" value="<?php echo $room_search_vars[ 'adult-quantity' ]; ?>" class="input-text" autocomplete="off" readonly="" data-min="1" data-max="50">
                                            <button class="plus"></button>
                                        </div>
                                    </div>

                                    <div class="csf-dropdown-item<?php if ( $hide_children ) : ?> hide<?php endif; ?>">
                                        <label class="cs-form-label"><?php esc_html_e( 'Children', 'loftocean' ); ?></label>

                                        <div class="quantity cs-quantity" data-label="child">
                                            <label class="screen-reader-text"><?php esc_html_e( 'Children quantity', 'loftocean' ); ?></label>
                                            <button class="minus"></button>
                                            <input type="text" name="child-quantity" value="<?php echo $room_search_vars[ 'child-quantity' ]; ?>" class="input-text" autocomplete="off" readonly="" data-min="0" data-max="50">
                                            <button class="plus"></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php do_action( 'loftocean_room_search_form_fields_after' ); ?>

                        <div class="cs-form-field cs-submit">
                            <div class="field-wrap">
                                <button type="submit" class="button"><span class="btn-text"><?php esc_html_e( 'Check Availability', 'loftocean' ); ?></span></button>
                            </div>
                        </div>
        				<input type="hidden" name="search_rooms" value="" />
                        <?php do_action( 'loftocean_search_form' ); ?>
                    </form>
                </div>
            </div>
        </aside>

    </div>
</div><?php

get_footer();

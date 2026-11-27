<div class="wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e( 'Room & Booking Settings', 'loftocean' ); ?></h1>
    <hr class="wp-header-end">
    <div class="">
        <div id="cs-dashboard-tabs-wrapper" class="nav-tab-wrapper cs-nav-tab-wrapper">
            <!-- <a id="cs-dashboard-tab-guests" class="nav-tab" href="#tab-guests"><?php esc_html_e( 'Guests', 'loftocean' ); ?></a>-->
            <a id="cs-dashboard-tab-advanced" class="nav-tab nav-tab-active"><?php esc_html_e( 'Advanced', 'loftocean' ); ?></a>
        </div>
        <div class="cs-tab-content-wrapper">
            <table class="form-table">
                <tbody>
                    <tr class="loftocean-room-regenerate-orders">
                        <th><?php esc_html_e( 'Sync Room Order Data', 'loftocean' ); ?></th>
                        <td>
                            <div class="multi-items-wrapper">
                                <button class="button cs-spinner-button"><?php esc_html_e( 'Sync Room Order Data', 'loftocean' ); ?></button>
                                <p class="message" style="display: none;"><?php esc_html_e( 'Failed. Please try again late.', 'loftocean' ); ?></p>
                            </div>

                            <p class="description"><?php esc_html_e( 'The number of remaining rooms in different language versions is automatically synchronized. You can also manually sync it by clicking on the sync button.', 'loftocean' ); ?></p>
                        </td>
                    </tr><?php
                    if ( apply_filters( 'loftocean_multilingual_website_enabled', false ) ) : ?>
                    <tr class="loftocean-room-reset-facilities">
                        <th><?php esc_html_e( 'Reset Room Facilities', 'loftocean' ); ?></th>
                        <td>
                            <div class="multi-items-wrapper">
                                <button class="button cs-spinner-button"><?php esc_html_e( 'Reset Room Facilities', 'loftocean' ); ?></button>
                                <p class="message" style="display: none;"><?php esc_html_e( 'Failed. Please try again late.', 'loftocean' ); ?></p>
                            </div>
                            <p class="description"><?php esc_html_e( 'Repeatedly changing settings of multilingual plugins may result in room facilities being set incorrectly. Click this button to reset room facilities.', 'loftocean' ); ?></p>
                        </td>
                    </tr><?php
                    endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

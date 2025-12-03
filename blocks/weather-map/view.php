<?php
/**
 * Template for displaying the weather map block.
 */
$block_props = get_block_wrapper_attributes();
?>

<div <?php echo $block_props; ?>>
    <div class="ws-app">
        <div class="ws-sidebar">
            <div class="ws-sidebar-header">
                <h2 class="ws-title">Weather Station</h2>
                <div class="ws-description">
                    <?php echo wp_kses_post($description); ?>
                </div>
            </div>
        </div>
        
        <div class="ws-map-container">
            <div id="weather-station-map"></div>
        </div>
    </div>
</div>
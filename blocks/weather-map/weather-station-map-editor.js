(function() {
    'use strict';
    
    console.log('Weather Station: Block editor script loaded at: ' + new Date().toISOString());
    
    // Debug: Check what's available
    console.log('wp available:', typeof wp !== 'undefined');
    if (typeof wp !== 'undefined') {
        console.log('wp.blocks:', typeof wp.blocks);
        console.log('wp.element:', typeof wp.element);
        console.log('wp.blockEditor:', typeof wp.blockEditor);
        console.log('wp.i18n:', typeof wp.i18n);
        console.log('wp.components:', typeof wp.components);
        
        // List all available wp properties
        var wpKeys = Object.keys(wp);
        console.log('Available wp properties:', wpKeys);
    }
    
    // Check if we have the minimum required dependencies
    if (typeof wp === 'undefined' || 
        typeof wp.blocks === 'undefined' || 
        typeof wp.element === 'undefined' ||
        typeof wp.blockEditor === 'undefined' ||
        typeof wp.i18n === 'undefined') {
        
        console.error('Weather Station: Required WordPress dependencies not available');
        return;
    }
    
    console.log('Weather Station: All dependencies available, registering block...');
    
    // Use the WordPress components
    var registerBlockType = wp.blocks.registerBlockType;
    var createElement = wp.element.createElement;
    var useBlockProps = wp.blockEditor.useBlockProps;
    var __ = wp.i18n.__;
    
    // Define the edit component
    var Edit = function() {
        console.log('Weather Station: Edit function called');
        
        var blockProps = useBlockProps({
            className: 'weather-station-map-block-editor',
        });
        
        return createElement('div', blockProps,
            createElement('div', {
                style: {
                    padding: '20px',
                    background: '#f0f0f0',
                    border: '2px dashed #007cba',
                    borderRadius: '4px',
                    textAlign: 'center'
                }
            },
                createElement('h3', {
                    style: {
                        margin: '0 0 10px 0',
                        color: '#007cba'
                    }
                }, __('Weather Station Map', 'weather-station')),
                createElement('p', {
                    style: {
                        margin: 0,
                        color: '#666'
                    }
                }, __('Interactive weather map will be displayed here on the frontend.', 'weather-station'))
            )
        );
    };
    
    // Register the block
    try {
        var result = registerBlockType('weather-station/weather-map', {
            title: __('Weather Station Map', 'weather-station'),
            category: 'widgets',
            icon: 'location-alt',
            edit: Edit,
            save: function() {
                // Return an empty div to match existing content
                return wp.element.createElement('div', {
                    className: 'wp-block-weather-station-weather-map'
                });
            }
        });
        
        console.log('Weather Station: Block registration result:', result);
        console.log('Weather Station: Block registered successfully!');
        
    } catch (error) {
        console.error('Weather Station: Failed to register block:', error);
    }
})();
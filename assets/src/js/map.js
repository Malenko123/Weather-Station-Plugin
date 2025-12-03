import BookmarkManager from './bookmark.js';

class WeatherStationMap {
    constructor() {
        if (!window.weatherStationData || !window.weatherStationData.stations) {
            console.error('Weather station data not available');
            return;
        }

        // State properties
        this.map = null;
        this.markers = [];
        this.activeStation = null;
        this.currentUnit = 'metric';
        this.currentWeatherData = null;

        // UI elements
        this.sidebarElement = null;
        this.weatherContainer = null;
        this.descriptionElement = null;

        // Instantiate and store the bookmark manager
        this.bookmarkManager = new BookmarkManager(this);

        this.init();
    }

    init() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.setupMap());
        } else {
            this.setupMap();
        }
    }

    setupMap() {
        const mapContainer = document.getElementById('weather-station-map');
        if (!mapContainer) {
            console.warn('Map container not found');
            return;
        }

        if ('scrollRestoration' in history) {
            history.scrollRestoration = 'manual';
        }

        this.initMap(mapContainer);
        this.initSidebar();
        this.addStationsToMap();
        this.setupEventListeners();
        this.checkUrlForStation();
    }
    
    // --- Map and Sidebar UI Methods ---
    initMap(mapContainer) {
        this.map = L.map(mapContainer).setView([51.505, -0.09], 3);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 18,
        }).addTo(this.map);
    }

    initSidebar() {
        const mapContainer = document.getElementById('weather-station-map');
        const appContainer = mapContainer.closest('.weather-station-app');
        
        if (appContainer) {
            this.sidebarElement = appContainer.querySelector('.weather-station-sidebar');
        }
        
        if (!this.sidebarElement) {
            console.warn('Sidebar element not found, creating temporary one');
            this.createTemporarySidebar();
        }

        this.descriptionElement = this.sidebarElement?.querySelector('.sidebar-description');
        if (this.descriptionElement && window.weatherStationData.sidebar_description) {
            this.descriptionElement.innerHTML = window.weatherStationData.sidebar_description;
        }
        
        this.weatherContainer = this.sidebarElement?.querySelector('.weather-data-container');
        if (!this.weatherContainer && this.sidebarElement) {
            this.weatherContainer = document.createElement('div');
            this.weatherContainer.className = 'weather-data-container';
            const sidebarContent = this.sidebarElement.querySelector('.sidebar-content');
            sidebarContent ? sidebarContent.appendChild(this.weatherContainer) : this.sidebarElement.appendChild(this.weatherContainer);
        }

        this.bookmarkManager.initMyLocations();
    }

    createTemporarySidebar() {
        this.sidebarElement = document.createElement('div');
        this.sidebarElement.className = 'weather-station-sidebar';
        
        this.weatherContainer = document.createElement('div');
        this.weatherContainer.className = 'weather-data-container';
        
        const sidebarContent = document.createElement('div');
        sidebarContent.className = 'sidebar-content';
        sidebarContent.appendChild(this.weatherContainer);
        this.sidebarElement.appendChild(sidebarContent);

        Object.assign(this.sidebarElement.style, {
            position: 'fixed', top: '20px', right: '20px', background: '#28282C',
            color: 'white', padding: '20px', borderRadius: '8px', zIndex: '1000', maxWidth: '300px'
        });
        document.body.appendChild(this.sidebarElement);
    }

    closeSidebar() {
        if (this.sidebarElement) {
            this.sidebarElement.classList.remove('has-active-station');
        }
        this.activeStation = null;
        this.currentWeatherData = null;
        
        this.markers.forEach(marker => {
            marker._icon?.classList.remove('active');
        });
        
        window.history.pushState({}, '', window.location.pathname);
    }

    updateBookmarkButtonState() {
        if (!this.weatherContainer || !this.activeStation) return;
        
        const isBookmarked = this.bookmarkManager.isBookmarked(this.activeStation);
        const bookmarkBtn = this.weatherContainer.querySelector('.js-ws-bookmark-btn');
        
        if (bookmarkBtn) {
            const bookmarkIconSrc = isBookmarked 
                ? window.weatherStationData.bookmark_filled_icon 
                : window.weatherStationData.bookmark_icon;
            
            // Update classes.
            if (isBookmarked) {
                bookmarkBtn.classList.add('ws-bookmarked');
            } else {
                bookmarkBtn.classList.remove('ws-bookmarked');
            }
            
            // Update icon.
            const icon = bookmarkBtn.querySelector('img');
            if (icon) {
                icon.src = bookmarkIconSrc;
                icon.alt = isBookmarked ? 'Bookmarked' : 'Bookmark';
            }
        }
    }

    addStationsToMap() {
        const greenPinIcon = L.icon({
            iconUrl: 'data:image/svg+xml;base64,' + btoa(`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path fill="#4CAF50" d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>`),
            iconSize: [24, 24], iconAnchor: [12, 24], popupAnchor: [0, -24], className: 'weather-station-marker'
        });

        window.weatherStationData.stations.forEach(station => {
            const marker = L.marker([station.lat, station.lng], { icon: greenPinIcon, stationId: station.id }).addTo(this.map);
            marker.on('click', () => this.activateStation(station.id));
            this.markers.push(marker);
        });
    }

    activateStation(stationId) {
        const station = window.weatherStationData.stations.find(s => s.id === stationId);
        if (!station) return;

        this.showWeatherData(stationId, station.lat, station.lng);
        this.activeStation = stationId;

        // --- Phase 2: Start MAP animation ---
        setTimeout(() => {
            this.map.invalidateSize();

            this.map.flyTo([station.lat, station.lng], 13, {
                animate: true,
                duration: 0.7,
                paddingTopLeft: [300, 0]
            });

            // --- Phase 3: AFTER map animation finishes, scroll and THEN push hash ---
            this.map.once('moveend', () => {
                const weatherSection = document.getElementById('weather-station-app');
                if (weatherSection) {
                    const scrollY = weatherSection.getBoundingClientRect().top + window.scrollY;
                    window.scrollTo({ top: scrollY, behavior: 'smooth' });

                    // Delay hash push until scroll finishes.
                    setTimeout(() => {
                        window.history.pushState({}, '', `${window.location.pathname}#${stationId}`);
                    }, 200);
                }
            });
        }, 150);

    }

    showWeatherData(stationId, lat, lng) {
        if (this.sidebarElement) {
            this.sidebarElement.classList.add('has-active-station');
        }
        if (this.descriptionElement) {
            this.descriptionElement.style.display = 'none';
        }
        this.fetchWeatherData(stationId, lat, lng);
    }
    
    // --- Data Fetching and Rendering ---
    fetchWeatherData(stationId, lat, lng) {
        if (this.weatherContainer) {
            this.weatherContainer.innerHTML = `<div class="weather-loading"><div class="loading-spinner"></div><p>Loading weather data...</p></div>`;
        }

        fetch(window.weatherStationData.ajaxurl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                action: 'get_weather_data', nonce: window.weatherStationData.nonce,
                station_id: stationId, lat: lat, lng: lng, units: this.currentUnit
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.currentWeatherData = data.data;
                this.updateSidebarWithWeatherData(data.data);
            } else {
                throw new Error(data.data || 'Unknown API error');
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            this.updateSidebarWithError('Network error: ' + error.message);
        });
    }

    updateSidebarWithWeatherData(weatherData) {
        if (!this.weatherContainer) return;

        this.updateSidebarHeaderVisibility(false);
        
        const unitSymbol = this.currentUnit === 'metric' ? '°C' : '°F';
        const windUnit = this.currentUnit === 'metric' ? 'm/s' : 'mph';

        // DELEGATE the check to the bookmark manager.
        const isBookmarked = this.bookmarkManager.isBookmarked(this.activeStation);
        const bookmarkIconSrc = isBookmarked ? window.weatherStationData.bookmark_filled_icon : window.weatherStationData.bookmark_icon;
        
        const weatherHTML = `
            <div class="ws-weather-card">
                <div class="ws-weather-header">
                    <div class="ws-weather-header-inner">
                        <div class="ws-temperature-controls">
                            <button class="ws-btn ${this.currentUnit === 'metric' ? 'ws-btn-active' : ''}" data-unit="metric">Celsius</button>
                            <span class="ws-unit-separator">/</span>
                            <button class="ws-btn ${this.currentUnit === 'imperial' ? 'ws-btn-active' : ''}" data-unit="imperial">Fahrenheit</button>
                        </div>
                        <div class="ws-bookmark-locations">
                            <button class="js-ws-bookmark-btn ${isBookmarked ? 'ws-bookmarked' : ''}" data-station-id="${this.activeStation}">
                                <img src="${bookmarkIconSrc}" class="ws-bookmark-icon" alt="${isBookmarked ? 'Bookmarked' : 'Bookmark'}" width="17" height="17" />
                            </button>
                        </div>
                    </div>
                    <h3 class="ws-location-name">${weatherData.name || 'Weather Station'}</h3>  
                </div>
                <div class="ws-weather-details">
                    <div class="ws-detail"><span class="ws-label">Weather: </span><span class="ws-value">${weatherData.weather[0].main} - ${weatherData.weather[0].description}</span></div>
                    <div class="ws-detail"><span class="ws-label">Temp: </span><span class="ws-value">${Math.round(weatherData.main.temp)}${unitSymbol} / Feels like ${Math.round(weatherData.main.feels_like)}${unitSymbol}</span></div>
                    <div class="ws-detail"><span class="ws-label">Pressure: </span><span class="ws-value">${weatherData.main.pressure} hPa</span></div>
                    <div class="ws-detail"><span class="ws-label">Humidity: </span><span class="ws-value">${weatherData.main.humidity}%</span></div>
                </div>
            </div>`;
        
        this.weatherContainer.innerHTML = weatherHTML;
        this.addUnitButtonListeners();
        this.addBookmarkButtonListeners();
    }
    
    updateSidebarWithError(errorMessage) {
        if (this.weatherContainer) {
            this.weatherContainer.innerHTML = `<div class="weather-data-active"><div class="weather-header"><h3>Error</h3></div><div class="weather-description">Failed to load weather data: ${errorMessage}</div></div>`;
        }
    }
    
    // --- Event Listeners and Handlers ---
    addBookmarkButtonListeners() {
        const bookmarkBtn = this.weatherContainer.querySelector('.js-ws-bookmark-btn');
        if (!bookmarkBtn) return;

        // Remove any stale listeners by cloning.
        const freshBtn = bookmarkBtn.cloneNode(true);
        bookmarkBtn.parentNode.replaceChild(freshBtn, bookmarkBtn);

        freshBtn.addEventListener('click', (e) => {
            const stationId = parseInt(e.currentTarget.dataset.stationId, 10);

            // Toggle in manager.
            this.bookmarkManager.toggleBookmark(stationId);

            // If we have data, re-render (this also rebinds listeners).
            if (this.currentWeatherData) {
                this.updateSidebarWithWeatherData(this.currentWeatherData);
            } else {
                // Fallback: at least sync the icon/class instantly.
                this.updateBookmarkButtonState();
            }
        });
    }

    addUnitButtonListeners() {
        this.weatherContainer.querySelectorAll('.ws-temperature-controls .ws-btn').forEach(button => {
            button.addEventListener('click', (e) => {
                const newUnit = e.currentTarget.dataset.unit;
                if (newUnit && newUnit !== this.currentUnit) {
                    this.currentUnit = newUnit;
                    const station = window.weatherStationData.stations.find(s => s.id === this.activeStation);
                    if (station) {
                        this.fetchWeatherData(this.activeStation, station.lat, station.lng);
                    }
                }
            });
        });
    }

    setupEventListeners() {
        this.map.on('click', (e) => {
            const closest = this.findClosestStation(e.latlng);
            if (closest) {
                this.activateStation(closest.id);
            } else {
                console.log("No station within threshold");
            }
        });

        window.addEventListener('popstate', () => this.checkUrlForStation());
    }

    findClosestStation(latlng) {
        let closestStation = null;
        let minDistance = Infinity;

        window.weatherStationData.stations.forEach(station => {
            const stationLatLng = L.latLng(station.lat, station.lng);
            const distance = this.map.distance(latlng, stationLatLng);

            if (distance < minDistance) {
                minDistance = distance;
                closestStation = station;
            }
        });

        return closestStation;
    }


    checkUrlForStation() {
        const stationId = parseInt(window.location.hash.substring(1), 10);
        if (!isNaN(stationId)) {
            this.activateStation(stationId);
        } else {
            this.closeSidebar();
        }
    }
    
    // This is the public method the bookmark manager needs to call.
    showStationWeather(stationId) {
        const station = window.weatherStationData.stations.find(s => s.id === stationId);
        if (station) {
            this.activateStation(stationId);
        }
    }

    addMapOverlay() {
        if (!this.mapOverlay) {
            this.mapOverlay = document.createElement('div');
            this.mapOverlay.className = 'ws-map-overlay';
            const mapContainer = document.getElementById('weather-station-map');
            if (mapContainer) {
                mapContainer.style.position = 'relative';
                mapContainer.appendChild(this.mapOverlay);
                
                // Trigger reflow to ensure animation works
                void this.mapOverlay.offsetWidth;
                
                // Add active class to trigger animation
                setTimeout(() => {
                    this.mapOverlay.classList.add('active');
                }, 10);
            }
        } else {
            // If overlay already exists, just activate it
            this.mapOverlay.classList.add('active');
        }
    }

    removeMapOverlay() {
        if (this.mapOverlay) {
            // Remove active class to trigger fade out
            this.mapOverlay.classList.remove('active');
            
            // Remove element from DOM after animation completes
            setTimeout(() => {
                if (this.mapOverlay && this.mapOverlay.parentNode) {
                    this.mapOverlay.parentNode.removeChild(this.mapOverlay);
                    this.mapOverlay = null;
                }
            }, 400); // Match this to your transition duration
        }
    }

    updateSidebarHeaderVisibility(show) {
        const header = this.sidebarElement.querySelector('.sidebar-header');
        if (header) {
            header.style.display = show ? '' : 'none';
        }
    }
}

// Initialize the entire application.
new WeatherStationMap();
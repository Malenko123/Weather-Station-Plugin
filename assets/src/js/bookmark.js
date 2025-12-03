export default class BookmarkManager {
    constructor(weatherStationMap) {
        this.weatherStationMap = weatherStationMap; // Reference to the main map class.

        // State properties
        this.bookmarks = this.loadBookmarks();
        this.showingBookmarks = false;
        this.bookmarkedStations = [];

        // UI elements
        this.bookmarksContainer = null;
        this.myLocationsBtn = null;
    }

    // --- Core Public Methods ---
    initMyLocations() {
        const locationsWrapper = document.createElement('div');
        locationsWrapper.className = 'ws-my-locations-wrapper';
        
        this.myLocationsBtn = document.createElement('button');
        this.myLocationsBtn.className = 'ws-my-locations-btn ws-btn';
        this.myLocationsBtn.innerHTML = `<span class="ws-my-locations-text">My Locations</span>`;
        this.myLocationsBtn.addEventListener('click', () => this.toggleBookmarksView());
        
        locationsWrapper.appendChild(this.myLocationsBtn);

        const sidebarContent = this.weatherStationMap.sidebarElement.querySelector('.sidebar-content');
        if (sidebarContent) {
            sidebarContent.appendChild(locationsWrapper);
        }
    }

    isBookmarked(stationId) {
        return this.bookmarks.includes(stationId);
    }

    toggleBookmark(stationId) {
        const index = this.bookmarks.indexOf(stationId);
        if (index > -1) {
            this.bookmarks.splice(index, 1);  // Remove the ID.
        } else {
            this.bookmarks.push(stationId);  // Add the ID.
        }
        this.saveBookmarks();
    }

    // --- UI and Event Handling ---
    toggleBookmarksView() {
        this.showingBookmarks = !this.showingBookmarks;
        
        if (this.showingBookmarks) {
            this.showBookmarks();
            this.myLocationsBtn.innerHTML = `<span class="ws-my-locations-text">Close</span>`;
            this.myLocationsBtn.classList.add('ws-showing-bookmarks');
        } else {
            this.hideBookmarks();
            this.myLocationsBtn.innerHTML = `<span class="ws-my-locations-text">My Locations</span>`;
            this.myLocationsBtn.classList.remove('ws-showing-bookmarks');
        }
    }

    showBookmarks() {
        this.bookmarkedStations = this.getBookmarkedStations();

        const { descriptionElement } = this.weatherStationMap;
        if (descriptionElement) descriptionElement.style.display = 'none';
        this.weatherStationMap.updateSidebarHeaderVisibility(false);

        this.bookmarksContainer = document.createElement('div');
        this.bookmarksContainer.className = 'ws-bookmarks-container';

        if (this.bookmarkedStations.length === 0) {
            this.bookmarksContainer.innerHTML = `
                <div class="ws-no-bookmarks">
                    <p>No saved locations yet</p>
                    <p>Click the bookmark icon on any weather station to save it here</p>
                </div>
            `;
        } else {
            let bookmarksHTML = `<div class="ws-bookmarks-list">`;

            // Show loading state for all bookmarks initially
            this.bookmarkedStations.forEach(station => {
                bookmarksHTML += `
                    <div class="ws-weather-card" data-station-id="${station.id}">
                        <div class="ws-weather-header">
                            <h3 class="ws-location-name">${station.name || 'Loading...'}</h3>
                        </div>
                        <div class="ws-weather-details">
                            <div class="weather-loading">
                                <div class="loading-spinner"></div>
                                <p>Loading weather data...</p>
                            </div>
                        </div>
                        <button class="ws-bookmark-remove" data-station-id="${station.id}">✕</button>
                    </div>
                `;
            });

            bookmarksHTML += `</div>`;
            this.bookmarksContainer.innerHTML = bookmarksHTML;

            // Fetch fresh data for each bookmarked station
            this.bookmarkedStations.forEach(station => {
                this.fetchFreshWeatherData(station.id, station.lat, station.lng);
            });

            this.addBookmarkItemListeners();
        }

        const sidebarContent = this.weatherStationMap.sidebarElement.querySelector('.sidebar-content');
        if (sidebarContent) sidebarContent.appendChild(this.bookmarksContainer);

        this.weatherStationMap.addMapOverlay();
    }

    fetchFreshWeatherData(stationId, lat, lng) {
        fetch(window.weatherStationData.ajaxurl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                action: 'get_weather_data', 
                nonce: window.weatherStationData.nonce,
                station_id: stationId, 
                lat: lat, 
                lng: lng, 
                units: this.weatherStationMap.currentUnit
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.updateBookmarkCard(stationId, data.data);
            } else {
                this.updateBookmarkCard(stationId, null, 'Failed to load data');
            }
        })
        .catch(error => {
            console.error('Fetch error for bookmark:', error);
            this.updateBookmarkCard(stationId, null, 'Network error');
        });
    }

    updateBookmarkCard(stationId, weatherData, errorMessage = null) {
        const card = this.bookmarksContainer.querySelector(`.ws-weather-card[data-station-id="${stationId}"]`);
        if (!card) return;

        const unitSymbol = this.weatherStationMap.currentUnit === 'metric' ? '°C' : '°F';
        
        if (errorMessage) {
            card.querySelector('.ws-weather-details').innerHTML = `
                <div class="weather-error">
                    <p>${errorMessage}</p>
                </div>
            `;
            return;
        }

        if (weatherData) {
            card.querySelector('.ws-location-name').textContent = weatherData.name || 'Unknown';
            
            const weatherHTML = `
                <div class="ws-detail"><span class="ws-label">Weather: </span><span class="ws-value">${weatherData.weather[0].main} - ${weatherData.weather[0].description}</span></div>
                <div class="ws-detail"><span class="ws-label">Temp: </span><span class="ws-value">${Math.round(weatherData.main.temp)}${unitSymbol} / Feels like ${Math.round(weatherData.main.feels_like)}${unitSymbol}</span></div>
                <div class="ws-detail"><span class="ws-label">Pressure: </span><span class="ws-value">${weatherData.main.pressure} hPa</span></div>
                <div class="ws-detail"><span class="ws-label">Humidity: </span><span class="ws-value">${weatherData.main.humidity}%</span></div>
            `;
            
            card.querySelector('.ws-weather-details').innerHTML = weatherHTML;
        }
    }

    hideBookmarks() {
        const { descriptionElement, activeStation } = this.weatherStationMap;

        if (activeStation) {
            if (descriptionElement) descriptionElement.style.display = 'none';
        } else {
            if (descriptionElement) descriptionElement.style.display = '';
        }

        if (this.bookmarksContainer) {
            this.bookmarksContainer.remove();
            this.bookmarksContainer = null;
        }

        this.myLocationsBtn.innerHTML = `<span class="ws-my-locations-text">My Locations</span>`;
        this.myLocationsBtn.classList.remove('ws-showing-bookmarks');
        this.showingBookmarks = false;

        this.weatherStationMap.removeMapOverlay();

        // Re-render the sidebar so DOM + listeners are guaranteed fresh.
        if (this.weatherStationMap.currentWeatherData) {
            this.weatherStationMap.updateSidebarWithWeatherData(this.weatherStationMap.currentWeatherData);
        } else if (activeStation) {
            const st = window.weatherStationData.stations.find(s => s.id === activeStation);
            if (st) {
                this.weatherStationMap.fetchWeatherData(st.id, st.lat, st.lng);
            }
        }

        // Extra safety: ensure the button visuals match bookmark state.
        this.weatherStationMap.updateBookmarkButtonState();
    }

   addBookmarkItemListeners() {
        // remove old listeners to prevent duplicates.
        this.bookmarksContainer.querySelectorAll('.ws-bookmark-remove').forEach(btn => {
            btn.replaceWith(btn.cloneNode(true));
        });
        this.bookmarksContainer.querySelectorAll('.ws-location-name').forEach(title => {
            title.replaceWith(title.cloneNode(true));
        });

        // remove bookmark handler.
        this.bookmarksContainer.querySelectorAll('.ws-bookmark-remove').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const stationId = parseInt(e.currentTarget.dataset.stationId, 10);
                this.removeBookmark(stationId);
            });
        });

        this.bookmarksContainer.addEventListener('click', (e) => {
            if (e.target.classList.contains('ws-location-name')) {
                const card = e.target.closest('.ws-weather-card');
                const details = card.querySelector('.ws-weather-details');
                const stationId = parseInt(card.dataset.stationId, 10);
                if (!details) return;

                const isOpening = details.style.display === 'none' || details.style.display === '';

                if (isOpening) {
                    // Close all other details first.
                    this.bookmarksContainer.querySelectorAll('.ws-weather-details').forEach(d => {
                        if (d !== details) {
                            d.style.maxHeight = "0px";
                            setTimeout(() => { d.style.display = 'none'; }, 300);
                        }
                    });

                    // Remove active/inactive from all cards.
                    this.bookmarksContainer.querySelectorAll('.ws-weather-card').forEach(c => c.classList.remove('active', 'inactive'));

                    // Open clicked card.
                    details.style.display = 'block';
                    details.style.maxHeight = details.scrollHeight + "px";
                    details.style.transition = "max-height 0.3s ease";
                    card.classList.add('active');

                    // Add inactive to all other cards.
                    this.bookmarksContainer.querySelectorAll('.ws-weather-card').forEach(c => {
                        if (c !== card) c.classList.add('inactive');
                    });

                    // Remove overlay when opening.
                    this.weatherStationMap.removeMapOverlay();

                    // Center map on this station.
                    const station = window.weatherStationData.stations.find(s => s.id === stationId);
                    if (station) {
                        this.weatherStationMap.map.setView([station.lat, station.lng], 13);
                        const marker = this.weatherStationMap.markers.find(m => m.options.stationId === stationId);
                        if (marker) marker.openPopup();
                    }

                    // Update URL hash.
                    if (history.pushState) {
                        history.pushState(null, null, `#${stationId}`);
                    } else {
                        location.hash = `#${stationId}`;
                    }

                } else {
                    // Close clicked details.
                    details.style.maxHeight = "0px";
                    setTimeout(() => { details.style.display = 'none'; }, 300);
                    card.classList.remove('active');

                    // Remove inactive from all if none active.
                    const anyActive = this.bookmarksContainer.querySelector('.ws-weather-card.active');
                    if (!anyActive) {
                        this.bookmarksContainer.querySelectorAll('.ws-weather-card').forEach(c => c.classList.remove('inactive'));
                    }

                    // Add overlay back when closing
                    this.weatherStationMap.addMapOverlay();
                }
            }
        });

        // hide all details by default
        this.bookmarksContainer.querySelectorAll('.ws-weather-details').forEach(d => {
            d.style.display = 'none';
            d.style.overflow = 'hidden';
            d.style.maxHeight = '0';
        });
    }

    removeBookmark(stationId) {
        // Remove from array
        this.bookmarks = this.bookmarks.filter(id => id !== stationId);
        this.saveBookmarks();

        // Remove from DOM list
        const card = this.bookmarksContainer.querySelector(`.ws-weather-card[data-station-id="${stationId}"]`);
        if (card) {
            card.remove();
        }

        // If no bookmarks left, show empty state
        if (this.bookmarks.length === 0) {
            this.bookmarksContainer.innerHTML = `
                <div class="ws-no-bookmarks">
                    <p>No saved locations yet</p>
                    <p>Click the bookmark icon on any weather station to save it here</p>
                </div>`;
        }

        // Also update the sidebar "active" card bookmark button if it's visible
        const sidebarBookmarkBtn = this.weatherStationMap.sidebarElement
            .querySelector(`.js-ws-bookmark-btn[data-station-id="${stationId}"]`);
        if (sidebarBookmarkBtn) {
            sidebarBookmarkBtn.classList.remove('ws-bookmarked');
            const img = sidebarBookmarkBtn.querySelector('img');
            if (img) {
                img.src = window.weatherStationData.bookmark_icon;
                img.alt = 'Bookmark';
            }
        }
    }

    updateBookmarksView() {
        const remainingItems = this.bookmarksContainer.querySelectorAll('.ws-bookmark-item');
        
        if (remainingItems.length === 0) {
            // Show empty state
            this.bookmarksContainer.innerHTML = `
                <div class="ws-no-bookmarks">
                    <p>No saved locations yet</p>
                    <p>Click the bookmark icon on any weather station to save it here</p>
                </div>`;
        }
    }

    // --- Data Handling ---
    getBookmarkedStations() {
        if (!window.weatherStationData?.stations) return [];
        return window.weatherStationData.stations.filter(station =>
            this.bookmarks.includes(station.id)
        );
    }

    loadBookmarks() {
        try {
            const saved = localStorage.getItem('ws_bookmarks');
            return saved ? JSON.parse(saved) : [];
        } catch (error) {
            console.error('Error loading bookmarks:', error);
            return [];
        }
    }

    saveBookmarks() {
        try {
            localStorage.setItem('ws_bookmarks', JSON.stringify(this.bookmarks));
        } catch (error) {
            console.error('Error saving bookmarks:', error);
        }
    }
}
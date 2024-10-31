// Google Maps API key
const GOOGLE_MAPS_API_KEY = 'AIzaSyABMOUhZaFdYKDd_aMISrx4HPmH70OD0gs';

// Get user's current location
function getLocation() {
    if (navigator.geolocation) {
        const options = {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0
        };

        navigator.geolocation.getCurrentPosition(
            showPosition, 
            handleError, 
            options
        );
    } else {
        alert("Geolocation is not supported by this browser.");
        fetchTuitionCenters(0, 0);
    }
}

let userLocation = { lat: null, lon: null };

// Display the user's location in the input field
function showPosition(position) {
    userLocation.lat = position.coords.latitude;
    userLocation.lon = position.coords.longitude;

    // Get detailed address information
    const geocoder = new google.maps.Geocoder();
    const latlng = { lat: userLocation.lat, lng: userLocation.lon };

    geocoder.geocode({ location: latlng }, (results, status) => {
        if (status === 'OK') {
            if (results[0]) {
                // Get the most accurate address
                const address = results[0].formatted_address;
                document.getElementById("searchLocation").value = address;
                
                // Update location on server with full address
                updateUserLocation(userLocation.lat, userLocation.lon, address);
                
                // Fetch tuition centers with accurate location
                fetchTuitionCenters(userLocation.lat, userLocation.lon);
            }
        }
    });
}

function updateUserLocation(lat, lon, address) {
    fetch('update_location.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `lat=${lat}&lon=${lon}&address=${address}`
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            console.error('Failed to update location');
        }
    })
    .catch(err => console.error(err));
}

// Haversine distance formula
function haversineDistance(lat1, lon1, lat2, lon2) {
    const R = 6371; // Radius of the Earth in kilometers
    const dLat = degreesToRadians(lat2 - lat1);
    const dLon = degreesToRadians(lon2 - lon1);
    const a = 
        Math.sin(dLat / 2) * Math.sin(dLat / 2) +
        Math.cos(degreesToRadians(lat1)) * Math.cos(degreesToRadians(lat2)) * 
        Math.sin(dLon / 2) * Math.sin(dLon / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return R * c; // Distance in kilometers
}

function degreesToRadians(degrees) {
    return degrees * (Math.PI / 180);
}

// Handle errors from geolocation
function handleError(error) {
    switch(error.code) {
        case error.PERMISSION_DENIED:
            alert("User denied the request for Geolocation.");
            break;
        case error.POSITION_UNAVAILABLE:
            alert("Location information is unavailable.");
            break;
        case error.TIMEOUT:
            alert("The request to get user location timed out.");
            break;
        case error.UNKNOWN_ERROR:
            alert("An unknown error occurred.");
            break;
    }
}

// Run the getLocation function on page load
window.onload = getLocation;

// Add search button click event
document.getElementById("search-btn").addEventListener("click", function () {
    const name = document.getElementById("search-name").value;
    const location = document.getElementById("search-location").value;

    // Handle search logic here
    console.log(`Searching for ${name} in ${location}`);
});

// Set the current year for the copyright
document.getElementById('currentYear').textContent = new Date().getFullYear();


// Add favorite functionality
document.querySelectorAll('.favorite-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        btn.classList.toggle('active');
        
        // Store the favorite status in local storage
        const tuitionName = btn.getAttribute('data-tuition-name');
        let favorites = JSON.parse(localStorage.getItem('favorites')) || [];
        
        if (favorites.includes(tuitionName)) {
            favorites = favorites.filter(name => name !== tuitionName); // Remove from favorites
        } else {
            favorites.push(tuitionName); // Add to favorites
        }
        
        localStorage.setItem('favorites', JSON.stringify(favorites));
    });
});

document.getElementById('search-btn').addEventListener('click', () => {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function (position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;

            fetchTuitionCenters(lat, lng);
        }, function (error) {
            console.error('Error fetching location:', error);
            // You can provide a default location or handle the error gracefully here
            fetchTuitionCenters(); // Fetch centers without user location
        });
    } else {
        console.error('Geolocation is not supported by this browser.');
        fetchTuitionCenters(); // Fetch centers without user location
    }
});

// Add this function to convert address to coordinates
async function geocodeAddress(address) {
    const geocoder = new google.maps.Geocoder();
    
    return new Promise((resolve, reject) => {
        geocoder.geocode({ address: address }, (results, status) => {
            if (status === 'OK') {
                const location = {
                    lat: results[0].geometry.location.lat(),
                    lng: results[0].geometry.location.lng()
                };
                resolve(location);
            } else {
                reject(`Geocoding failed: ${status}`);
            }
        });
    });
}

// Update the fetchTuitionCenters function
async function fetchTuitionCenters(lat, lon) {
    try {
        const service = new google.maps.DistanceMatrixService();
        const response = await fetch('fetch_tuition.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `lat=${lat}&lon=${lon}`
        });
        
        const centers = await response.json();
        
        // Process each center
        for (const center of centers) {
            if (center.latitude && center.longitude && lat && lon) {
                try {
                    const result = await new Promise((resolve, reject) => {
                        service.getDistanceMatrix({
                            origins: [{ lat: parseFloat(lat), lng: parseFloat(lon) }],
                            destinations: [{ lat: parseFloat(center.latitude), lng: parseFloat(center.longitude) }],
                            travelMode: google.maps.TravelMode.DRIVING,
                            unitSystem: google.maps.UnitSystem.METRIC
                        }, (response, status) => {
                            if (status === 'OK') {
                                resolve(response);
                            } else {
                                reject(status);
                            }
                        });
                    });

                    if (result.rows[0].elements[0].status === 'OK') {
                        // Get the actual driving distance
                        center.distance = (result.rows[0].elements[0].distance.value / 1000).toFixed(1);
                    } else {
                        center.distance = 'N/A';
                    }
                } catch (error) {
                    console.error('Error calculating distance:', error);
                    center.distance = 'N/A';
                }
            } else {
                center.distance = 'N/A';
            }
        }

        displayTuitionCenters(centers);
    } catch (err) {
        console.error('Error fetching tuition centers:', err);
    }
}

function displayTuitionCenters(centers) {
    const resultsContainer = document.getElementById('recommendation-grid');
    resultsContainer.innerHTML = '';

    centers.forEach(center => {
        const resultCard = document.createElement('div');
        resultCard.classList.add('tuition-center-card');

        resultCard.innerHTML = `
            <button class="favorite-btn" data-center-id="${center.id}" aria-label="Add to favorites">
                <i class="fas fa-heart"></i>
            </button>
            <img src="${center.image}" alt="${center.name}">
            <div class="card-content">
                <h3>${center.name}</h3>
                <p class="rating">Rating: ${center.avg_rating ? parseFloat(center.avg_rating).toFixed(1) : 'N/A'}/5</p>
                <p class="distance">Distance: ${center.distance !== 'N/A' ? center.distance + ' km' : 'N/A'}</p>
                <a href="tuition_details.php?id=${center.id}" class="btn btn-primary details-btn">Details</a>
            </div>
        `;

        resultsContainer.appendChild(resultCard);
    });

    initializeFavoriteButtons();
}

function initializeFavoriteButtons() {
    document.querySelectorAll('.favorite-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            btn.classList.toggle('active');
            const centerId = btn.getAttribute('data-center-id');
            toggleFavorite(centerId);
        });
    });
}

function toggleFavorite(centerId) {
    fetch('toggle_favorite.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `center_id=${centerId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log(data.message);
        } else {
            console.error('Failed to toggle favorite:', data.error);
        }
    })
    .catch(err => console.error('Error toggling favorite:', err));
}

// Add this function to periodically update the user's location
function startLocationTracking() {
    setInterval(() => {
        getLocation();
    }, 60000); // Update every minute
}

// Call this function when the page loads
document.addEventListener('DOMContentLoaded', function() {
    getLocation();
    startLocationTracking();
});

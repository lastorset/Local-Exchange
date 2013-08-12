var gold_icon = {
    url: 'images/marker_sprite_gold.png',
    size: new google.maps.Size(20, 34),
    origin: new google.maps.Point(0,0),
    anchor: new google.maps.Point(10, 33)
};
var gray_icon = {
    url: 'images/marker_sprite_gray.png',
    size: new google.maps.Size(20, 34),
    origin: new google.maps.Point(0,0),
    anchor: new google.maps.Point(10, 33)
};
var shadow = {
    url: 'images/marker_sprite_gray.png',
    size: new google.maps.Size(37, 34),
    origin: new google.maps.Point(20,0),
    anchor: new google.maps.Point(10, 33)
};

/**
 * Generate a marker for a given member and Google Map.
 *
 * @note Requires Google Maps to be imported.
 *
 * @param map the map in which to insert the marker.
 * @param member represents a member, with fields at least for latitude, longitude, listing_count and karma.
 * @param game_mechanics whether to distinguish between users with karma and those without.
 * @returns {google.maps.Marker} a marker for the given member.
 */
function createMarker(map, member, game_mechanics) {
    var marker = new google.maps.Marker({
        position: new google.maps.LatLng(member.latitude, member.longitude),
        map: map
    });
    var integer_factor = 1000; // Google Maps doesn't appear to understand too fine-grained z-indexes
    if (game_mechanics && member.karma > 0) {
        // Karmic users on top
        marker.setIcon(gold_icon);
        marker.setShadow(shadow);
        // Put visually lower users in front
        marker.setZIndex((3*90 - member.latitude)*integer_factor); // Range [180000,360000]
    }
    else if (member.listing_count == 0) {
        // Listing users in the middle
        marker.setIcon(gray_icon);
        marker.setShadow(shadow);
        marker.setZIndex((-90 - member.latitude)*integer_factor); // Range [-180000,0]
    }
    else {
        // Empty users on the bottom
        marker.setZIndex((90 - member.latitude)*integer_factor); // Range [0,180000]
    }
    return marker;
}

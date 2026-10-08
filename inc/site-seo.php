<?php
/**
 * Shared SEO layer for the satellite artist sites.
 *
 * Driven by satellite_site_config() (defined in functions.php of each theme):
 *   brand        – artist / brand name (logo alt, modal, schema)
 *   show         – [en, ru] phrase used in the booking success message
 *   booking_slug – EN slug of the booking / contact page
 *   nodes        – schema entities: key => [type, name, page (EN slug),
 *                  job => [en, ru] (Person), member_of / members => keys,
 *                  same_as_instagram => bool]
 *   main         – key of the node that publishes the site, performs at
 *                  events and is what the front page is about
 *
 * Adds:
 * - one entity graph on top of Yoast (stable @ids /#<key>, Yoast's
 *   user-based "Person, Organization" node replaced and its references
 *   rewired; ProfilePage on each entity's own page and its translations);
 * - MusicEvent nodes for upcoming Schedule dates on the Tour Dates
 *   template (only data that exists: date, place, ticket link);
 * - helpers for the booking email and upcoming dates.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function satellite_cfg( $key = null ) {
	$cfg = function_exists( 'satellite_site_config' ) ? satellite_site_config() : [];
	return null === $key ? $cfg : ( $cfg[ $key ] ?? null );
}

function satellite_is_ru() {
	return function_exists( 'pll_current_language' ) && pll_current_language() === 'ru';
}

/** Booking email from Theme Options (Contact, then Footer); empty when not set. */
function satellite_booking_email() {
	if ( ! function_exists( 'get_field' ) ) return '';
	$contact = get_field( 'contact', 'option' );
	$email   = is_array( $contact ) ? ( $contact['email'] ?? '' ) : '';
	if ( ! $email ) $email = (string) get_field( 'footer_email', 'option' );
	return is_email( $email ) ? $email : '';
}

/** A page given by its EN slug, in the current language (0 when missing). */
function satellite_page_id( $slug ) {
	if ( ! $slug ) return 0;
	$page = get_page_by_path( $slug );
	if ( ! $page ) return 0;
	return (int) ( function_exists( 'pll_get_post' ) ? ( pll_get_post( $page->ID ) ?: $page->ID ) : $page->ID );
}

/** IDs of a page (EN slug) in every language. */
function satellite_page_ids_all_langs( $slug ) {
	$page = $slug ? get_page_by_path( $slug ) : null;
	if ( ! $page ) return [];
	$ids   = function_exists( 'pll_get_post_translations' ) ? array_map( 'intval', (array) pll_get_post_translations( $page->ID ) ) : [];
	$ids[] = (int) $page->ID;
	return array_values( array_unique( $ids ) );
}

/** Upcoming schedule items (options page), sorted, as [ ts, item ] pairs. */
function satellite_upcoming_schedule() {
	if ( ! function_exists( 'get_field' ) ) return [];
	$items = get_field( 'schedule_items', 'option' );
	if ( ! $items || ! is_array( $items ) ) return [];
	$today = strtotime( 'today' );
	$out   = [];
	foreach ( $items as $item ) {
		$parts = explode( '/', $item['date'] ?? '' );
		if ( count( $parts ) !== 3 ) continue;
		$ts = strtotime( "{$parts[2]}-{$parts[1]}-{$parts[0]}" );
		if ( $ts && $ts >= $today ) $out[] = [ $ts, $item ];
	}
	usort( $out, fn( $a, $b ) => $a[0] - $b[0] );
	return $out;
}

/** ISO 3166-1 alpha-2 code for the country at the end of a place text, '' when unknown. */
function satellite_country_code( $text ) {
	static $map = [
		'austria' => 'AT', 'italy' => 'IT', 'estonia' => 'EE', 'kazakhstan' => 'KZ', 'germany' => 'DE',
		'switzerland' => 'CH', 'france' => 'FR', 'spain' => 'ES', 'portugal' => 'PT', 'poland' => 'PL',
		'latvia' => 'LV', 'lithuania' => 'LT', 'finland' => 'FI', 'sweden' => 'SE', 'norway' => 'NO',
		'denmark' => 'DK', 'netherlands' => 'NL', 'belgium' => 'BE', 'luxembourg' => 'LU', 'monaco' => 'MC',
		'czech republic' => 'CZ', 'czechia' => 'CZ', 'slovakia' => 'SK', 'hungary' => 'HU', 'slovenia' => 'SI',
		'croatia' => 'HR', 'serbia' => 'RS', 'romania' => 'RO', 'bulgaria' => 'BG', 'greece' => 'GR',
		'cyprus' => 'CY', 'malta' => 'MT', 'turkey' => 'TR', 'israel' => 'IL', 'united arab emirates' => 'AE',
		'uae' => 'AE', 'united kingdom' => 'GB', 'uk' => 'GB', 'england' => 'GB', 'ireland' => 'IE', 'usa' => 'US',
		'united states' => 'US', 'canada' => 'CA', 'ukraine' => 'UA', 'moldova' => 'MD', 'georgia' => 'GE',
		'armenia' => 'AM', 'azerbaijan' => 'AZ', 'uzbekistan' => 'UZ', 'kyrgyzstan' => 'KG', 'belarus' => 'BY',
		'russia' => 'RU', 'mongolia' => 'MN', 'china' => 'CN', 'japan' => 'JP', 'australia' => 'AU',
		'south africa' => 'ZA', 'brazil' => 'BR', 'mexico' => 'MX', 'argentina' => 'AR', 'india' => 'IN',
	];
	$parts = array_map( 'trim', explode( ',', strtolower( (string) $text ) ) );
	return $map[ end( $parts ) ] ?? '';
}

function satellite_schema_id( $key ) {
	return untrailingslashit( get_option( 'home' ) ) . '/#' . $key;
}

add_filter( 'wpseo_schema_graph', function ( $graph, $context ) {
	$nodes = satellite_cfg( 'nodes' );
	$main  = satellite_cfg( 'main' );
	if ( ! is_array( $graph ) || ! $nodes || ! $main ) return $graph;

	$is_ru   = satellite_is_ru();
	$main_id = satellite_schema_id( $main );

	// Drop Yoast's user-based person/organization node, remember its @id and image.
	$old_ids   = [];
	$old_image = null;
	foreach ( $graph as $i => $node ) {
		$types = (array) ( $node['@type'] ?? [] );
		if ( in_array( 'Person', $types, true ) || ( in_array( 'Organization', $types, true ) && ! in_array( 'WebSite', $types, true ) ) ) {
			$old_ids[] = $node['@id'] ?? '';
			$old_image = $node['image'] ?? $old_image;
			unset( $graph[ $i ] );
		}
	}
	$graph   = array_values( $graph );
	$old_ids = array_filter( $old_ids );
	if ( $old_ids ) {
		array_walk_recursive( $graph, function ( &$value, $key ) use ( $old_ids, $main_id ) {
			if ( '@id' === $key && in_array( $value, $old_ids, true ) ) $value = $main_id;
		} );
	}

	$insta = function_exists( 'get_field' ) ? get_field( 'instagram', 'option' ) : null;
	$insta = is_array( $insta ) && ! empty( $insta['url'] ) ? esc_url_raw( $insta['url'] ) : '';

	$queried  = (int) get_queried_object_id();
	$entities = [];
	foreach ( $nodes as $key => $n ) {
		$e = [ '@type' => $n['type'], '@id' => satellite_schema_id( $key ), 'name' => $n['name'] ];
		if ( ! empty( $n['page'] ) && ( $pid = satellite_page_id( $n['page'] ) ) ) {
			$e['url'] = get_permalink( $pid );
			$img      = get_the_post_thumbnail_url( $pid, 'full' );
			if ( $img ) $e['image'] = [ '@type' => 'ImageObject', 'url' => $img, 'contentUrl' => $img ];
		} else {
			$e['url'] = untrailingslashit( get_option( 'home' ) ) . '/';
		}
		if ( empty( $e['image'] ) && $key === $main && $old_image ) $e['image'] = $old_image;
		if ( ! empty( $n['job'] ) ) $e['jobTitle'] = $is_ru ? $n['job'][1] : $n['job'][0];
		if ( ! empty( $n['member_of'] ) ) $e['memberOf'] = array_map( fn( $k ) => [ '@id' => satellite_schema_id( $k ) ], (array) $n['member_of'] );
		if ( ! empty( $n['members'] ) ) $e['member'] = array_map( fn( $k ) => [ '@id' => satellite_schema_id( $k ) ], (array) $n['members'] );
		if ( ! empty( $n['same_as_instagram'] ) && $insta ) $e['sameAs'] = [ $insta ];
		if ( ! empty( $n['page'] ) && in_array( $queried, satellite_page_ids_all_langs( $n['page'] ), true ) && is_page() ) {
			$e['mainEntityOfPage'] = [ '@id' => get_permalink( $queried ) ];
			foreach ( $graph as &$node ) {
				if ( in_array( 'WebPage', (array) ( $node['@type'] ?? [] ), true ) ) {
					$node['@type']      = [ 'WebPage', 'ProfilePage' ];
					$node['mainEntity'] = [ '@id' => $e['@id'] ];
				}
			}
			unset( $node );
		}
		$entities[ $key ] = $e;
	}

	foreach ( $graph as &$node ) {
		$types = (array) ( $node['@type'] ?? [] );
		if ( in_array( 'WebSite', $types, true ) ) {
			$node['publisher'] = [ '@id' => $main_id ];
			$node['about']     = [ '@id' => $main_id ];
		}
		if ( in_array( 'WebPage', $types, true ) && is_front_page() ) {
			$node['about'] = [ '@id' => $main_id ];
		}
	}
	unset( $node );
	foreach ( $entities as $e ) $graph[] = $e;

	// Tour Dates page: one MusicEvent per upcoming date.
	if ( is_page_template( 'template-tour.php' ) ) {
		$url  = get_permalink( $queried );
		$img  = $entities[ $main ]['image']['url'] ?? '';
		$name = satellite_cfg( 'brand' );
		$n    = 0;
		foreach ( satellite_upcoming_schedule() as [ $ts, $item ] ) {
			$place_name = ( $is_ru && ! empty( $item['text_ru'] ) ) ? $item['text_ru'] : ( $item['text'] ?? '' );
			if ( ! $place_name ) continue;
			$place = [ '@type' => 'Place', 'name' => $place_name ];
			if ( $code = satellite_country_code( $item['text'] ?? '' ) ) {
				$place['address'] = [ '@type' => 'PostalAddress', 'addressCountry' => $code ];
			}
			$event = [
				'@type'               => 'MusicEvent',
				'@id'                 => $url . '#event-' . gmdate( 'Y-m-d', $ts ) . '-' . ( ++$n ),
				'name'                => $name . ' — ' . ( $item['text'] ?? $place_name ),
				'startDate'           => gmdate( 'Y-m-d', $ts ),
				'eventStatus'         => 'https://schema.org/EventScheduled',
				'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
				'location'            => $place,
				'performer'           => [ '@id' => $main_id ],
				'url'                 => $url,
			];
			if ( $img ) $event['image'] = [ $img ];
			if ( ! empty( $item['link'] ) ) $event['offers'] = [ '@type' => 'Offer', 'url' => esc_url_raw( $item['link'] ) ];
			$graph[] = $event;
		}
	}

	return $graph;
}, 20, 2 );

/**
 * Polylang prints x-default only when browser language detection is on.
 * Point x-default at the default-language (English) version of every page
 * that has translations.
 */
add_filter( 'pll_rel_hreflang_attributes', function ( $hreflangs ) {
	if ( isset( $hreflangs['x-default'] ) || ! function_exists( 'pll_default_language' ) ) return $hreflangs;
	$default = pll_default_language( 'slug' );
	foreach ( $hreflangs as $code => $url ) {
		if ( $code === $default || strpos( $code, $default . '-' ) === 0 ) {
			$hreflangs['x-default'] = $url;
			break;
		}
	}
	return $hreflangs;
} );

/**
 * The static front page answers /page/N/ with a copy of itself (canonical /),
 * which only produces duplicate URLs: send those back to the front page.
 */
add_action( 'template_redirect', function () {
	if ( ! is_front_page() || is_preview() ) return;
	$paged = max( (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
	if ( $paged < 2 ) return;
	wp_safe_redirect( function_exists( 'pll_home_url' ) ? pll_home_url() : home_url( '/' ), 301 );
	exit;
}, 5 );

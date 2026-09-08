/**
 * Client-side sanitisation for static block `save()` output.
 *
 * `save()` is serialised straight into `post_content`, so user-supplied URLs and
 * CSS values must be validated here the same way `esc_url()` / a conservative
 * CSS filter would on the server. The Elementor widget applies the equivalent
 * PHP helpers so both editors emit identical, safe markup.
 */

const BENTO_ALLOWED_URL_PROTOCOLS = [ 'http:', 'https:', 'mailto:', 'tel:' ];

const BENTO_UNSAFE_CSS = /[;{}<>\\]|url\(|@import|expression\(|javascript:/i;

/**
 * Return `value` when it is a safe URL (http/https/mailto/tel, protocol-relative,
 * relative path, query, or fragment), otherwise an empty string.
 */
export function bentoSafeUrl( value: unknown ): string {
	const url = typeof value === 'string' ? value.trim() : '';

	if ( ! url ) {
		return '';
	}

	if ( /^(#|\/|\.\/|\.\.\/|\?)/.test( url ) ) {
		return url;
	}

	try {
		const parsed = new URL( url, 'https://bento.invalid' );

		return BENTO_ALLOWED_URL_PROTOCOLS.includes( parsed.protocol ) ? url : '';
	} catch {
		return '';
	}
}

/**
 * Return `value` when it is a single, safe CSS value (a colour or a gradient
 * function), otherwise an empty string. Anything that could break out of an
 * inline declaration or pull in a resource is rejected.
 */
export function bentoSafeCssValue( value: unknown ): string {
	const css = typeof value === 'string' ? value.trim() : '';

	if ( ! css || BENTO_UNSAFE_CSS.test( css ) ) {
		return '';
	}

	return css;
}

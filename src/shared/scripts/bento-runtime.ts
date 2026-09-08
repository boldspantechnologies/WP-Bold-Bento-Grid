/**
 * Bento Grid — client-side runtime engine.
 *
 * Lightweight, dependency-free frontend engine that powers hover/tilt
 * interactions for rendered grids. Deliberately avoids importing
 * Framer Motion here: that library is React-oriented and the AGENT.md
 * Lite/Pro split (rules 8-9) reserves heavier 3D/Framer Motion effects
 * for a later, explicitly gated phase. This engine relies on GPU-cheap
 * CSS custom properties + `transform`, updated via a single delegated,
 * rAF-throttled pointermove listener so it stays fast on low-end mobile.
 *
 * Single source of truth: works against the same `.bento-grid-wrapper`
 * / `.bento-grid__tile` markup emitted by both save.tsx (Gutenberg) and
 * the Elementor widget render(), per AGENT.md 7.
 */

const BENTO_WRAPPER_SELECTOR = '.bento-grid-wrapper';
const BENTO_TILE_SELECTOR = '.bento-grid__tile';

const BENTO_MAX_TILT_DEG = 8;
const BENTO_HOVER_SCALE = 1.02;

const bentoPrefersReducedMotion = (): boolean =>
	typeof window !== 'undefined' &&
	typeof window.matchMedia === 'function' &&
	window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

/**
 * Resets a tile's tilt/scale custom properties back to their resting
 * state. Reads are avoided here on purpose; this only ever writes.
 */
function bentoResetTile( tile: HTMLElement ): void {
	tile.style.setProperty( '--bento-tilt-x', '0deg' );
	tile.style.setProperty( '--bento-tilt-y', '0deg' );
	tile.style.setProperty( '--bento-tilt-scale', '1' );
}

/**
 * Computes and applies tilt/scale custom properties for a tile based on
 * the pointer position relative to its bounding box.
 */
function bentoApplyTilt( tile: HTMLElement, clientX: number, clientY: number ): void {
	const rect = tile.getBoundingClientRect();

	if ( rect.width === 0 || rect.height === 0 ) {
		return;
	}

	const offsetX = ( clientX - rect.left ) / rect.width; // 0..1
	const offsetY = ( clientY - rect.top ) / rect.height; // 0..1

	const rotateY = ( offsetX - 0.5 ) * 2 * BENTO_MAX_TILT_DEG;
	const rotateX = ( 0.5 - offsetY ) * 2 * BENTO_MAX_TILT_DEG;

	tile.style.setProperty( '--bento-tilt-x', `${ rotateX.toFixed( 2 ) }deg` );
	tile.style.setProperty( '--bento-tilt-y', `${ rotateY.toFixed( 2 ) }deg` );
	tile.style.setProperty( '--bento-tilt-scale', String( BENTO_HOVER_SCALE ) );
}

/**
 * Bootstraps the Bento Grid runtime: attaches a small number of
 * document-level, event-delegated listeners rather than binding a
 * listener per tile, so cost stays flat regardless of how many grids
 * or tiles are on the page.
 */
function bentoInitRuntime(): void {
	const wrappers = document.querySelectorAll< HTMLElement >( BENTO_WRAPPER_SELECTOR );

	if ( wrappers.length === 0 ) {
		return;
	}

	// Respect user motion preferences: skip tilt tracking entirely, but
	// still mark wrappers as initialized for any CSS-only hover states.
	const reducedMotion = bentoPrefersReducedMotion();

	wrappers.forEach( ( wrapper ) => {
		wrapper.setAttribute( 'data-bento-runtime', 'active' );
	} );

	if ( reducedMotion ) {
		return;
	}

	let activeTile: HTMLElement | null = null;
	let pendingEvent: PointerEvent | null = null;
	let rafId = 0;

	const flush = () => {
		rafId = 0;

		if ( ! activeTile || ! pendingEvent ) {
			return;
		}

		bentoApplyTilt( activeTile, pendingEvent.clientX, pendingEvent.clientY );
	};

	const scheduleFlush = () => {
		if ( rafId !== 0 ) {
			return;
		}

		rafId = window.requestAnimationFrame( flush );
	};

	document.addEventListener(
		'pointermove',
		( event: PointerEvent ) => {
			if ( event.pointerType === 'touch' ) {
				return;
			}

			const tile = ( event.target as HTMLElement | null )?.closest< HTMLElement >(
				BENTO_TILE_SELECTOR
			);

			if ( ! tile || ! tile.closest( BENTO_WRAPPER_SELECTOR ) ) {
				if ( activeTile ) {
					bentoResetTile( activeTile );
					activeTile = null;
				}
				return;
			}

			if ( tile !== activeTile ) {
				if ( activeTile ) {
					bentoResetTile( activeTile );
				}
				activeTile = tile;
			}

			pendingEvent = event;
			scheduleFlush();
		},
		{ passive: true }
	);

	// `pointerout` bubbles, unlike `pointerleave`, so a single delegated
	// listener can detect when the pointer fully leaves a tile.
	document.addEventListener(
		'pointerout',
		( event: PointerEvent ) => {
			if ( ! activeTile ) {
				return;
			}

			const relatedTarget = event.relatedTarget as HTMLElement | null;

			if ( relatedTarget && activeTile.contains( relatedTarget ) ) {
				return;
			}

			bentoResetTile( activeTile );
			activeTile = null;
			pendingEvent = null;
		},
		{ passive: true }
	);
}

if ( typeof document !== 'undefined' ) {
	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', bentoInitRuntime, { once: true } );
	} else {
		bentoInitRuntime();
	}
}

export { bentoInitRuntime };

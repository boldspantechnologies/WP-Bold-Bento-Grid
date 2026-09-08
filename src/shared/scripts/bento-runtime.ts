const BENTO_WRAPPER_SELECTOR = '.bento-grid-wrapper';
const BENTO_TILE_SELECTOR = '.bento-grid__tile';

const BENTO_MAX_TILT_DEG = 8;
const BENTO_HOVER_SCALE = 1.02;

const bentoPrefersReducedMotion = (): boolean =>
	typeof window !== 'undefined' &&
	typeof window.matchMedia === 'function' &&
	window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

function bentoResetTile( tile: HTMLElement ): void {
	tile.style.setProperty( '--bento-tilt-x', '0deg' );
	tile.style.setProperty( '--bento-tilt-y', '0deg' );
	tile.style.setProperty( '--bento-tilt-scale', '1' );
}

function bentoApplyTilt( tile: HTMLElement, clientX: number, clientY: number ): void {
	const rect = tile.getBoundingClientRect();

	if ( rect.width === 0 || rect.height === 0 ) {
		return;
	}

	const offsetX = ( clientX - rect.left ) / rect.width;
	const offsetY = ( clientY - rect.top ) / rect.height;

	const rotateY = ( offsetX - 0.5 ) * 2 * BENTO_MAX_TILT_DEG;
	const rotateX = ( 0.5 - offsetY ) * 2 * BENTO_MAX_TILT_DEG;

	tile.style.setProperty( '--bento-tilt-x', `${ rotateX.toFixed( 2 ) }deg` );
	tile.style.setProperty( '--bento-tilt-y', `${ rotateY.toFixed( 2 ) }deg` );
	tile.style.setProperty( '--bento-tilt-scale', String( BENTO_HOVER_SCALE ) );
}

function bentoInitRuntime(): void {
	const wrappers = document.querySelectorAll< HTMLElement >( BENTO_WRAPPER_SELECTOR );

	if ( wrappers.length === 0 ) {
		return;
	}

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

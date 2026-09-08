import { InnerBlocks, RichText, useBlockProps } from '@wordpress/block-editor';
import type { BlockSaveProps } from '@wordpress/blocks';
import type { CSSProperties, ReactNode } from 'react';

import { bentoSafeUrl } from '../../shared/sanitize';
import { bentoTileClassName, bentoTileStyle } from './edit';
import type { BentoTileAttributes } from './edit';

const BENTO_DEFAULT_OVERLAY_OPACITY = 40;

function BentoTileTextSave( { title, caption }: { title: string; caption: string } ) {
	if ( ! title && ! caption ) {
		return null;
	}

	return (
		<div className="bento-tile__text">
			{ title && (
				<RichText.Content tagName="h3" className="bento-tile__title" value={ title } />
			) }
			{ caption && (
				<RichText.Content tagName="p" className="bento-tile__caption" value={ caption } />
			) }
		</div>
	);
}

function BentoTileMediaSave( {
	mediaUrl,
	mediaAlt,
	overlay = false,
	overlayOpacity = BENTO_DEFAULT_OVERLAY_OPACITY,
}: {
	mediaUrl: string;
	mediaAlt: string;
	overlay?: boolean;
	overlayOpacity?: number;
} ) {
	if ( ! mediaUrl ) {
		return null;
	}

	return (
		<div className="bento-tile__media">
			<img
				className="bento-tile__img"
				src={ mediaUrl }
				alt={ mediaAlt }
				loading="lazy"
			/>
			{ overlay && (
				<span
					className="bento-tile__scrim"
					aria-hidden="true"
					style={ { opacity: overlayOpacity / 100 } as CSSProperties }
				/>
			) }
		</div>
	);
}

export default function BentoTileSave( {
	attributes,
}: BlockSaveProps< BentoTileAttributes > ) {
	const {
		contentMode,
		mediaUrl,
		mediaAlt,
		title,
		caption,
		linkUrl,
		overlayOpacity,
		hoverEffect,
	} = attributes;

	const safeMediaUrl = bentoSafeUrl( mediaUrl );
	const safeLinkUrl = bentoSafeUrl( linkUrl );
	const hasMedia = Boolean( safeMediaUrl );

	// Accessible name for the tile, derived from the author's title. Plain text
	// (the title RichText allows no formats), so it is safe as an attribute.
	const ariaLabel = title ? title.trim() || undefined : undefined;

	const blockProps = useBlockProps.save( {
		className: bentoTileClassName( contentMode, hasMedia, hoverEffect ),
		style: bentoTileStyle( attributes ),
		role: ariaLabel ? 'group' : undefined,
		'aria-label': ariaLabel,
	} );

	const textBlock = (
		<div className="bento-tile__body">
			<BentoTileTextSave title={ title } caption={ caption } />
		</div>
	);

	let inner: ReactNode;

	if ( contentMode === 'custom-blocks' ) {
		inner = (
			<div className="bento-tile__custom">
				<InnerBlocks.Content />
			</div>
		);
	} else if ( contentMode === 'image-overlay' ) {
		inner = (
			<>
				<BentoTileMediaSave
					mediaUrl={ safeMediaUrl }
					mediaAlt={ mediaAlt }
					overlay
					overlayOpacity={ overlayOpacity }
				/>
				{ textBlock }
			</>
		);
	} else if ( contentMode === 'text-top' ) {
		inner = (
			<>
				{ textBlock }
				<BentoTileMediaSave mediaUrl={ safeMediaUrl } mediaAlt={ mediaAlt } />
			</>
		);
	} else {
		inner = (
			<>
				<BentoTileMediaSave mediaUrl={ safeMediaUrl } mediaAlt={ mediaAlt } />
				{ textBlock }
			</>
		);
	}

	if ( safeLinkUrl && contentMode !== 'custom-blocks' ) {
		return (
			<div { ...blockProps }>
				<a
					className="bento-tile__link"
					href={ safeLinkUrl }
					aria-label={ ariaLabel }
				>
					{ inner }
				</a>
			</div>
		);
	}

	return <div { ...blockProps }>{ inner }</div>;
}

import { __ } from '@wordpress/i18n';
import {
	BlockControls,
	InnerBlocks,
	InspectorControls,
	MediaPlaceholder,
	MediaUpload,
	MediaUploadCheck,
	PanelColorSettings,
	RichText,
	useBlockProps,
} from '@wordpress/block-editor';
import {
	Button,
	PanelBody,
	RangeControl,
	SelectControl,
	TextControl,
	ToolbarButton,
	ToolbarGroup,
} from '@wordpress/components';
import type { BlockEditProps } from '@wordpress/blocks';
import type { CSSProperties, ReactNode } from 'react';

import { bentoSafeCssValue } from '../../shared/sanitize';

export type BentoTileContentMode =
	| 'image-overlay'
	| 'text-top'
	| 'text-bottom'
	| 'custom-blocks';

export interface BentoTileAttributes {
	contentMode: BentoTileContentMode;
	mediaUrl: string;
	mediaId: number;
	mediaAlt: string;
	title: string;
	caption: string;
	linkUrl: string;
	overlayOpacity: number;
	hoverEffect: string;
	bgColor: string;
	bgGradient: string;
	textColor: string;
	borderWidth: number;
	borderColor: string;
	[ key: string ]: unknown;
}

interface BentoMedia {
	id: number;
	url?: string;
	alt?: string;
}

const BENTO_CONTENT_MODES = [
	{ value: 'image-overlay', label: __( 'Image overlay', 'bold-bento-grid' ), icon: 'cover-image' },
	{ value: 'text-top', label: __( 'Text on top', 'bold-bento-grid' ), icon: 'arrow-up-alt2' },
	{ value: 'text-bottom', label: __( 'Text below', 'bold-bento-grid' ), icon: 'arrow-down-alt2' },
	{ value: 'custom-blocks', label: __( 'Custom blocks', 'bold-bento-grid' ), icon: 'block-default' },
] as const;

export const BENTO_HOVER_EFFECTS: Array< { value: string; label: string } > = [
	{ value: 'none', label: __( 'None', 'bold-bento-grid' ) },
	{ value: 'lift', label: __( 'Lift', 'bold-bento-grid' ) },
	{ value: 'glow', label: __( 'Glow', 'bold-bento-grid' ) },
	{ value: 'zoom', label: __( 'Image zoom', 'bold-bento-grid' ) },
	{ value: 'tilt', label: __( '3D tilt', 'bold-bento-grid' ) },
];

const BENTO_DEFAULT_OVERLAY_OPACITY = 40;
const BENTO_MAX_BORDER_WIDTH = 8;

/**
 * Class list for a tile root element. Kept in one place so `edit` and `save`
 * (and the Elementor widget, which mirrors this exactly) never drift.
 */
export function bentoTileClassName(
	contentMode: string,
	hasMedia: boolean,
	hoverEffect: string
): string {
	const classes = [
		'bento-grid__tile',
		'bento-tile',
		`bento-mode-${ contentMode }`,
		hasMedia ? 'has-media' : 'is-empty',
	];

	if ( hoverEffect && hoverEffect !== 'none' ) {
		classes.push( `bento-hover-${ hoverEffect }` );
	}

	return classes.join( ' ' );
}

/**
 * Inline custom properties that carry the per-tile design overrides. Only set
 * the ones the user actually changed so the CSS defaults keep working.
 */
export function bentoTileStyle( attributes: Partial< BentoTileAttributes > ): CSSProperties {
	const style: Record< string, string > = {};

	const background =
		bentoSafeCssValue( attributes.bgGradient ) || bentoSafeCssValue( attributes.bgColor );
	if ( background ) {
		style[ '--bento-tile-bg' ] = background;
	}

	const textColor = bentoSafeCssValue( attributes.textColor );
	if ( textColor ) {
		style[ '--bento-tile-text' ] = textColor;
	}

	const borderWidth = Math.max( 0, Math.min( 20, Number( attributes.borderWidth ) || 0 ) );
	if ( borderWidth > 0 ) {
		style[ '--bento-tile-border-width' ] = `${ borderWidth }px`;

		const borderColor = bentoSafeCssValue( attributes.borderColor );
		if ( borderColor ) {
			style[ '--bento-tile-border-color' ] = borderColor;
		}
	}

	return style as CSSProperties;
}

function BentoTileText( {
	title,
	caption,
	setAttributes,
}: {
	title: string;
	caption: string;
	setAttributes: ( attrs: Partial< BentoTileAttributes > ) => void;
} ) {
	return (
		<div className="bento-tile__text">
			<RichText
				identifier="title"
				tagName="h3"
				className="bento-tile__title"
				value={ title }
				onChange={ ( value ) => setAttributes( { title: value } ) }
				placeholder={ __( 'Add a title…', 'bold-bento-grid' ) }
				allowedFormats={ [] }
			/>
			<RichText
				identifier="caption"
				tagName="p"
				className="bento-tile__caption"
				value={ caption }
				onChange={ ( value ) => setAttributes( { caption: value } ) }
				placeholder={ __( 'Add a caption…', 'bold-bento-grid' ) }
				allowedFormats={ [ 'core/bold', 'core/italic' ] }
			/>
		</div>
	);
}

function BentoTileMedia( {
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
	return (
		<div className="bento-tile__media">
			<img className="bento-tile__img" src={ mediaUrl } alt={ mediaAlt } />
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

export default function BentoTileEdit( {
	attributes,
	setAttributes,
}: BlockEditProps< BentoTileAttributes > ) {
	const {
		contentMode,
		mediaUrl,
		mediaId,
		mediaAlt,
		title,
		caption,
		linkUrl,
		overlayOpacity,
		hoverEffect,
		bgColor,
		bgGradient,
		textColor,
		borderWidth,
		borderColor,
	} = attributes;

	const hasMedia = Boolean( mediaUrl );
	const isCustom = contentMode === 'custom-blocks';
	const isOverlay = contentMode === 'image-overlay';

	const blockProps = useBlockProps( {
		className: bentoTileClassName( contentMode, hasMedia, hoverEffect ),
		style: bentoTileStyle( attributes ),
	} );

	const bentoOnSelectMedia = ( media: BentoMedia ) => {
		if ( ! media || ! media.url ) {
			return;
		}

		setAttributes( {
			mediaId: media.id,
			mediaUrl: media.url,
			mediaAlt: media.alt || '',
		} );
	};

	const bentoOnResetMedia = () => {
		setAttributes( { mediaId: 0, mediaUrl: '', mediaAlt: '' } );
	};

	const bentoSetMode = ( mode: BentoTileContentMode ) => {
		setAttributes( { contentMode: mode } );
	};

	const textBlock = (
		<div className="bento-tile__body">
			<BentoTileText title={ title } caption={ caption } setAttributes={ setAttributes } />
		</div>
	);

	let canvas: ReactNode;

	if ( isCustom ) {
		canvas = (
			<div className="bento-tile__custom">
				<InnerBlocks templateLock={ false } />
			</div>
		);
	} else if ( ! hasMedia ) {
		canvas = (
			<MediaPlaceholder
				icon="plus"
				labels={ {
					title: __( 'Tile image', 'bold-bento-grid' ),
					instructions: __(
						'Drag an image here, upload, or choose from your library.',
						'bold-bento-grid'
					),
				} }
				accept="image/*"
				allowedTypes={ [ 'image' ] }
				onSelect={ bentoOnSelectMedia }
				className="bento-tile__placeholder"
			/>
		);
	} else if ( isOverlay ) {
		canvas = (
			<>
				<BentoTileMedia
					mediaUrl={ mediaUrl }
					mediaAlt={ mediaAlt }
					overlay
					overlayOpacity={ overlayOpacity }
				/>
				{ textBlock }
			</>
		);
	} else if ( contentMode === 'text-top' ) {
		canvas = (
			<>
				{ textBlock }
				<BentoTileMedia mediaUrl={ mediaUrl } mediaAlt={ mediaAlt } />
			</>
		);
	} else {
		canvas = (
			<>
				<BentoTileMedia mediaUrl={ mediaUrl } mediaAlt={ mediaAlt } />
				{ textBlock }
			</>
		);
	}

	return (
		<>
			<BlockControls>
				<ToolbarGroup>
					{ BENTO_CONTENT_MODES.map( ( mode ) => (
						<ToolbarButton
							key={ mode.value }
							icon={ mode.icon }
							label={ mode.label }
							isActive={ contentMode === mode.value }
							onClick={ () => bentoSetMode( mode.value ) }
						/>
					) ) }
				</ToolbarGroup>

				{ ! isCustom && hasMedia && (
					<ToolbarGroup>
						<MediaUploadCheck>
							<MediaUpload
								onSelect={ bentoOnSelectMedia }
								allowedTypes={ [ 'image' ] }
								value={ mediaId }
								render={ ( { open } ) => (
									<ToolbarButton
										icon="edit"
										label={ __( 'Replace image', 'bold-bento-grid' ) }
										onClick={ open }
									/>
								) }
							/>
						</MediaUploadCheck>
					</ToolbarGroup>
				) }
			</BlockControls>

			<InspectorControls>
				<PanelBody title={ __( 'Content mode', 'bold-bento-grid' ) } initialOpen={ true }>
					<div className="bento-tile__mode-control">
						{ BENTO_CONTENT_MODES.map( ( mode ) => (
							<Button
								key={ mode.value }
								icon={ mode.icon }
								variant={ contentMode === mode.value ? 'primary' : 'secondary' }
								onClick={ () => bentoSetMode( mode.value ) }
							>
								{ mode.label }
							</Button>
						) ) }
					</div>
				</PanelBody>

				{ ! isCustom && (
					<PanelBody title={ __( 'Media', 'bold-bento-grid' ) } initialOpen={ true }>
						<MediaUploadCheck>
							<MediaUpload
								onSelect={ bentoOnSelectMedia }
								allowedTypes={ [ 'image' ] }
								value={ mediaId }
								render={ ( { open } ) => (
									<Button variant="secondary" onClick={ open }>
										{ hasMedia
											? __( 'Replace image', 'bold-bento-grid' )
											: __( 'Set image', 'bold-bento-grid' ) }
									</Button>
								) }
							/>
						</MediaUploadCheck>

						{ hasMedia && (
							<>
								<TextControl
									label={ __( 'Alt text', 'bold-bento-grid' ) }
									value={ mediaAlt }
									onChange={ ( value ) => setAttributes( { mediaAlt: value } ) }
								/>
								<Button variant="link" isDestructive onClick={ bentoOnResetMedia }>
									{ __( 'Remove image', 'bold-bento-grid' ) }
								</Button>
							</>
						) }
					</PanelBody>
				) }

				{ isOverlay && (
					<PanelBody title={ __( 'Overlay', 'bold-bento-grid' ) } initialOpen={ false }>
						<RangeControl
							label={ __( 'Scrim opacity', 'bold-bento-grid' ) }
							value={ overlayOpacity }
							onChange={ ( value ) =>
								setAttributes( {
									overlayOpacity: value ?? BENTO_DEFAULT_OVERLAY_OPACITY,
								} )
							}
							min={ 0 }
							max={ 100 }
							step={ 5 }
						/>
					</PanelBody>
				) }

				<PanelBody title={ __( 'Interaction & border', 'bold-bento-grid' ) } initialOpen={ false }>
					<SelectControl
						label={ __( 'Hover effect', 'bold-bento-grid' ) }
						value={ hoverEffect }
						options={ BENTO_HOVER_EFFECTS.map( ( effect ) => ( {
							label: effect.label,
							value: effect.value,
						} ) ) }
						onChange={ ( value ) => setAttributes( { hoverEffect: value } ) }
					/>

					<RangeControl
						label={ __( 'Border width', 'bold-bento-grid' ) }
						value={ borderWidth }
						onChange={ ( value ) => setAttributes( { borderWidth: value ?? 0 } ) }
						min={ 0 }
						max={ BENTO_MAX_BORDER_WIDTH }
						step={ 1 }
					/>

					<TextControl
						label={ __( 'Background gradient (CSS)', 'bold-bento-grid' ) }
						help={ __(
							'e.g. linear-gradient(135deg, #6366f1, #ec4899). Overrides the background colour.',
							'bold-bento-grid'
						) }
						value={ bgGradient }
						onChange={ ( value ) => setAttributes( { bgGradient: value } ) }
					/>
				</PanelBody>

				<PanelColorSettings
					title={ __( 'Colors', 'bold-bento-grid' ) }
					colorSettings={ [
						{
							value: bgColor,
							onChange: ( value?: string ) =>
								setAttributes( { bgColor: value ?? '' } ),
							label: __( 'Background', 'bold-bento-grid' ),
						},
						{
							value: textColor,
							onChange: ( value?: string ) =>
								setAttributes( { textColor: value ?? '' } ),
							label: __( 'Text', 'bold-bento-grid' ),
						},
						{
							value: borderColor,
							onChange: ( value?: string ) =>
								setAttributes( { borderColor: value ?? '' } ),
							label: __( 'Border', 'bold-bento-grid' ),
						},
					] }
				/>

				<PanelBody title={ __( 'Link', 'bold-bento-grid' ) } initialOpen={ false }>
					<TextControl
						label={ __( 'Link URL', 'bold-bento-grid' ) }
						type="url"
						value={ linkUrl }
						placeholder="https://"
						onChange={ ( value ) => setAttributes( { linkUrl: value } ) }
					/>
					{ isCustom && linkUrl && (
						<p className="bento-tile__hint">
							{ __(
								'Links are ignored while the tile uses custom blocks.',
								'bold-bento-grid'
							) }
						</p>
					) }
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>{ canvas }</div>
		</>
	);
}

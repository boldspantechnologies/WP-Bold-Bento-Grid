import { __ } from '@wordpress/i18n';
import {
	InnerBlocks,
	InspectorControls,
	PanelColorSettings,
	useBlockProps,
	store as blockEditorStore,
} from '@wordpress/block-editor';
import { Button, Modal, Notice, PanelBody, RangeControl } from '@wordpress/components';
import { useSelect, useDispatch } from '@wordpress/data';
import { createBlock } from '@wordpress/blocks';
import type { BlockEditProps } from '@wordpress/blocks';
import { useEffect, useState } from 'react';
import type { CSSProperties } from 'react';

type BentoTemplate = Array< [ string, Record< string, unknown >? ] >;

import { bentoGridDefaultAttributes } from '../shared';
import { bentoSafeCssValue } from '../shared/sanitize';

/**
 * Inline custom properties for the grid wrapper. Shared by `edit` and `save`
 * (and mirrored by the Elementor widget) so the markup is byte-identical, with
 * numeric ranges clamped and the colour passed through the CSS-value filter.
 */
export function bentoGridStyle(
	gap: number,
	borderRadius: number,
	bgColor: string
): CSSProperties {
	const clamp = ( value: number, max: number ) =>
		Math.max( 0, Math.min( max, Math.round( Number( value ) || 0 ) ) );

	return {
		'--bento-gap': `${ clamp( gap, 200 ) }px`,
		'--bento-radius': `${ clamp( borderRadius, 200 ) }px`,
		'--bento-bg-color': bentoSafeCssValue( bgColor ) || bentoGridDefaultAttributes.bgColor,
	} as CSSProperties;
}

declare global {
	interface Window {
		bentoGridPro?: { isPro: boolean; url: string };
	}
}

const BENTO_PRO_URL = 'https://bentogrid.boldspan.tech';

const BENTO_PRO_COPY = __(
	'Unlock 6+ Tile Layouts, Glassmorphism, Dynamic Product Grids, & Hover Animations in Bento Grid Pro',
	'bold-bento-grid'
);

/** Presets that only exist in Bento Grid Pro — shown locked in Lite. */
const BENTO_LOCKED_PRESETS = [
	{
		name: 'glassmorphism-8',
		label: __( 'Glassmorphism 8', 'bold-bento-grid' ),
		columns: 4,
		rows: 2,
		cells: [
			[ 1, 2, 1, 2 ],
			[ 2, 3, 1, 2 ],
			[ 3, 4, 1, 2 ],
			[ 4, 5, 1, 2 ],
			[ 1, 2, 2, 3 ],
			[ 2, 3, 2, 3 ],
			[ 3, 4, 2, 3 ],
			[ 4, 5, 2, 3 ],
		] as Array< [ number, number, number, number ] >,
	},
	{
		name: 'woo-auto-grid',
		label: __( 'WooCommerce Auto-Fetch Grid', 'bold-bento-grid' ),
		columns: 3,
		rows: 2,
		cells: [
			[ 1, 2, 1, 2 ],
			[ 2, 3, 1, 2 ],
			[ 3, 4, 1, 2 ],
			[ 1, 2, 2, 3 ],
			[ 2, 3, 2, 3 ],
			[ 3, 4, 2, 3 ],
		] as Array< [ number, number, number, number ] >,
	},
	{
		name: 'interactive-product-bento',
		label: __( 'Interactive Product Bento', 'bold-bento-grid' ),
		columns: 12,
		rows: 2,
		cells: [
			[ 1, 7, 1, 3 ],
			[ 7, 13, 1, 2 ],
			[ 7, 10, 2, 3 ],
			[ 10, 13, 2, 3 ],
		] as Array< [ number, number, number, number ] >,
	},
];

const BENTO_TILE_BLOCK_NAME = 'bold-bento/tile';

const BENTO_MIN_TILES = 2;
const BENTO_MAX_TILES = 5;

const BENTO_MIN_GAP = 0;
const BENTO_MAX_GAP = 40;

const BENTO_MIN_RADIUS = 0;
const BENTO_MAX_RADIUS = 32;

const BENTO_TILE_TEMPLATE: BentoTemplate = [
	[ BENTO_TILE_BLOCK_NAME ],
	[ BENTO_TILE_BLOCK_NAME ],
	[ BENTO_TILE_BLOCK_NAME ],
];

const BentoNoAppender = () => null;

export interface BentoGridBlockAttributes {
	layoutPreset: string;
	tileCount: number;
	gap: number;
	borderRadius: number;
	bgColor: string;
	[ key: string ]: unknown;
}

/**
 * A preset maps 1:1 to a `[data-bento-grid="<name>"]` rule in the CSS grid
 * engine (see `src/blocks/style.scss`). `cells` mirrors the engine's tile
 * placement as `[ colStart, colEnd, rowStart, rowEnd ]` grid lines so the
 * Inspector can draw an accurate thumbnail.
 *
 * Presets are grouped by `tileCount`; the layout auto-adjust logic keeps the
 * chosen preset valid for the current number of tiles.
 */
interface BentoPresetDefinition {
	name: string;
	label: string;
	tileCount: number;
	columns: number;
	rows: number;
	cells: Array< [ number, number, number, number ] >;
}

const BENTO_PRESETS: BentoPresetDefinition[] = [
	/* 2 tiles */
	{
		name: 'split-2',
		label: __( 'Split 50/50', 'bold-bento-grid' ),
		tileCount: 2,
		columns: 2,
		rows: 1,
		cells: [
			[ 1, 2, 1, 2 ],
			[ 2, 3, 1, 2 ],
		],
	},
	{
		name: 'asymmetric-2',
		label: __( 'Asymmetric 70/30', 'bold-bento-grid' ),
		tileCount: 2,
		columns: 10,
		rows: 1,
		cells: [
			[ 1, 8, 1, 2 ],
			[ 8, 11, 1, 2 ],
		],
	},

	/* 3 tiles */
	{
		name: 'hero-3',
		label: __( 'Hero Focus', 'bold-bento-grid' ),
		tileCount: 3,
		columns: 12,
		rows: 2,
		cells: [
			[ 1, 8, 1, 3 ],
			[ 8, 13, 1, 2 ],
			[ 8, 13, 2, 3 ],
		],
	},
	{
		name: 'triple-row-3',
		label: __( 'Triple Row', 'bold-bento-grid' ),
		tileCount: 3,
		columns: 3,
		rows: 1,
		cells: [
			[ 1, 2, 1, 2 ],
			[ 2, 3, 1, 2 ],
			[ 3, 4, 1, 2 ],
		],
	},

	/* 4 tiles */
	{
		name: 'balanced-4',
		label: __( 'Balanced Quad', 'bold-bento-grid' ),
		tileCount: 4,
		columns: 12,
		rows: 2,
		cells: [
			[ 1, 7, 1, 2 ],
			[ 7, 13, 1, 2 ],
			[ 1, 7, 2, 3 ],
			[ 7, 13, 2, 3 ],
		],
	},
	{
		name: 'showcase-4',
		label: __( 'Showcase 4', 'bold-bento-grid' ),
		tileCount: 4,
		columns: 12,
		rows: 2,
		cells: [
			[ 1, 9, 1, 2 ],
			[ 9, 13, 1, 3 ],
			[ 1, 5, 2, 3 ],
			[ 5, 9, 2, 3 ],
		],
	},

	/* 5 tiles */
	{
		name: 'feature-5',
		label: __( 'Feature 5', 'bold-bento-grid' ),
		tileCount: 5,
		columns: 12,
		rows: 2,
		cells: [
			[ 1, 7, 1, 3 ],
			[ 7, 10, 1, 2 ],
			[ 10, 13, 1, 2 ],
			[ 7, 10, 2, 3 ],
			[ 10, 13, 2, 3 ],
		],
	},
	{
		name: 'asymmetric-5',
		label: __( 'Asymmetric 5', 'bold-bento-grid' ),
		tileCount: 5,
		columns: 12,
		rows: 2,
		cells: [
			[ 1, 7, 1, 3 ],
			[ 7, 13, 1, 2 ],
			[ 7, 9, 2, 3 ],
			[ 9, 11, 2, 3 ],
			[ 11, 13, 2, 3 ],
		],
	},
	{
		name: 'strip-5',
		label: __( 'Strip 5', 'bold-bento-grid' ),
		tileCount: 5,
		columns: 5,
		rows: 1,
		cells: [
			[ 1, 2, 1, 2 ],
			[ 2, 3, 1, 2 ],
			[ 3, 4, 1, 2 ],
			[ 4, 5, 1, 2 ],
			[ 5, 6, 1, 2 ],
		],
	},
];

const BENTO_DEFAULT_PRESET = BENTO_PRESETS[ 0 ];

function bentoFindPreset( name: string ): BentoPresetDefinition {
	return BENTO_PRESETS.find( ( preset ) => preset.name === name ) || BENTO_DEFAULT_PRESET;
}

/** Presets whose geometry is designed for exactly `count` tiles. */
function bentoPresetsForCount( count: number ): BentoPresetDefinition[] {
	return BENTO_PRESETS.filter( ( preset ) => preset.tileCount === count );
}

function bentoClampTileCount( value: number ): number {
	return Math.max( BENTO_MIN_TILES, Math.min( BENTO_MAX_TILES, Math.round( value ) ) );
}

function BentoPresetThumbnail( { preset }: { preset: BentoPresetDefinition } ) {
	return (
		<span
			className="bento-preset-picker__preview"
			style={
				{
					gridTemplateColumns: `repeat(${ preset.columns }, 1fr)`,
					gridTemplateRows: `repeat(${ preset.rows }, 1fr)`,
				} as CSSProperties
			}
		>
			{ preset.cells.map( ( [ colStart, colEnd, rowStart, rowEnd ], index ) => (
				<span
					key={ index }
					className="bento-preset-picker__cell"
					style={
						{
							gridColumn: `${ colStart } / ${ colEnd }`,
							gridRow: `${ rowStart } / ${ rowEnd }`,
						} as CSSProperties
					}
				/>
			) ) }
		</span>
	);
}

function BentoPresetPicker( {
	value,
	presets,
	onSelect,
}: {
	value: string;
	presets: BentoPresetDefinition[];
	onSelect: ( preset: BentoPresetDefinition ) => void;
} ) {
	return (
		<div
			className="bento-preset-picker"
			role="radiogroup"
			aria-label={ __( 'Layout preset', 'bold-bento-grid' ) }
		>
			{ presets.map( ( preset ) => {
				const isActive = preset.name === value;

				return (
					<button
						key={ preset.name }
						type="button"
						role="radio"
						aria-checked={ isActive }
						className={
							'bento-preset-picker__option' + ( isActive ? ' is-active' : '' )
						}
						onClick={ () => onSelect( preset ) }
					>
						<BentoPresetThumbnail preset={ preset } />
						<span className="bento-preset-picker__label">{ preset.label }</span>
					</button>
				);
			} ) }
		</div>
	);
}

export default function BentoGridEdit( {
	attributes,
	setAttributes,
	clientId,
}: BlockEditProps< BentoGridBlockAttributes > ) {
	const {
		layoutPreset = bentoGridDefaultAttributes.layoutPreset,
		tileCount = bentoGridDefaultAttributes.tileCount,
		gap = bentoGridDefaultAttributes.gap,
		borderRadius = bentoGridDefaultAttributes.borderRadius,
		bgColor = bentoGridDefaultAttributes.bgColor,
	} = attributes;

	const tileClientIds = useSelect(
		( select ) => select( blockEditorStore ).getBlockOrder( clientId ),
		[ clientId ]
	);

	const currentTileCount = tileClientIds ? tileClientIds.length : 0;

	const { insertBlocks, removeBlocks } = useDispatch( blockEditorStore );

	const bentoPro = typeof window !== 'undefined' ? window.bentoGridPro : undefined;
	const isPro = Boolean( bentoPro && bentoPro.isPro );
	const proUrl = ( bentoPro && bentoPro.url ) || BENTO_PRO_URL;

	const [ proModalOpen, setProModalOpen ] = useState( false );

	/**
	 * Layout auto-adjust.
	 *
	 * Runs whenever `tileCount` or `layoutPreset` changes (and whenever the
	 * inner block list changes underneath us):
	 *
	 *  1. If the active preset is not designed for the current `tileCount`,
	 *     switch to the first valid preset for that count.
	 *  2. Reconcile the number of `bold-bento/tile` inner blocks with
	 *     `tileCount` — insert blanks when short, truncate the excess when long
	 *     — so the grid geometry always has exactly the tiles it expects.
	 */
	useEffect( () => {
		const validPresets = bentoPresetsForCount( tileCount );

		if ( validPresets.length > 0 && ! validPresets.some( ( p ) => p.name === layoutPreset ) ) {
			setAttributes( { layoutPreset: validPresets[ 0 ].name } );
		}

		if ( ! tileClientIds ) {
			return;
		}

		if ( currentTileCount < tileCount ) {
			const additions = Array.from(
				{ length: tileCount - currentTileCount },
				() => createBlock( BENTO_TILE_BLOCK_NAME )
			);
			insertBlocks( additions, currentTileCount, clientId, false );
		} else if ( currentTileCount > tileCount ) {
			removeBlocks( tileClientIds.slice( tileCount ), false );
		}
	}, [
		layoutPreset,
		tileCount,
		tileClientIds,
		currentTileCount,
		clientId,
		insertBlocks,
		removeBlocks,
		setAttributes,
	] );

	const bentoOnChangeTileCount = ( value: number | undefined ) => {
		if ( typeof value !== 'number' ) {
			return;
		}

		// Guardrail: 6+ tiles are a Pro feature. Dragging past 5 opens the upsell
		// instead of committing an unsupported count.
		if ( value > BENTO_MAX_TILES ) {
			if ( ! isPro ) {
				setProModalOpen( true );
			}
			return;
		}

		setAttributes( { tileCount: bentoClampTileCount( value ) } );
	};

	const bentoOnSelectPreset = ( preset: BentoPresetDefinition ) => {
		setAttributes( { layoutPreset: preset.name, tileCount: preset.tileCount } );
	};

	const blockProps = useBlockProps( {
		className: 'bold-bento-wrapper',
		'data-bento-grid': layoutPreset,
		style: bentoGridStyle( gap, borderRadius, bgColor ),
	} );

	const activePreset = bentoFindPreset( layoutPreset );
	const presetsForCount = bentoPresetsForCount( tileCount );
	const tilesOutOfSync = currentTileCount > tileCount;

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Layout', 'bold-bento-grid' ) } initialOpen={ true }>
					<div className="bento-pro-range">
						<RangeControl
							label={ __( 'Number of Tiles', 'bold-bento-grid' ) }
							value={ tileCount }
							onChange={ bentoOnChangeTileCount }
							min={ BENTO_MIN_TILES }
							max={ 8 }
							step={ 1 }
							help={
								isPro
									? undefined
									: __( 'Tiles 6–8 require Bento Grid Pro.', 'bold-bento-grid' )
							}
						/>

						{ ! isPro && (
							<button
								type="button"
								className="bento-pro-range__locked"
								onClick={ () => setProModalOpen( true ) }
								aria-label={ __( 'Unlock 6+ tiles with Bento Grid Pro', 'bold-bento-grid' ) }
							>
								<span className="bento-pro-range__steps">
									<span>6</span>
									<span>7</span>
									<span>8</span>
								</span>
								<span className="bento-pro-badge">
									{ __( 'PRO', 'bold-bento-grid' ) } 🔒
								</span>
							</button>
						) }
					</div>

					<BentoPresetPicker
						value={ activePreset.name }
						presets={ presetsForCount }
						onSelect={ bentoOnSelectPreset }
					/>

					<p className="bento-preset-picker__hint">
						{ __(
							'Presets are matched to the tile count. Use the slider to add or remove tiles.',
							'bold-bento-grid'
						) }
					</p>

					{ tilesOutOfSync && (
						<Notice status="warning" isDismissible={ false }>
							{ __(
								'Extra tiles beyond the selected count will be removed to keep the layout intact.',
								'bold-bento-grid'
							) }
						</Notice>
					) }

					{ ! isPro && (
						<div className="bento-pro-presets">
							<p className="bento-pro-presets__title">
								{ __( 'Locked Pro presets', 'bold-bento-grid' ) }
							</p>

							<div className="bento-pro-presets__grid">
								{ BENTO_LOCKED_PRESETS.map( ( preset ) => (
									<button
										key={ preset.name }
										type="button"
										className="bento-pro-presets__option"
										onClick={ () => setProModalOpen( true ) }
									>
										<span
											className="bento-preset-picker__preview bento-pro-presets__preview"
											style={
												{
													gridTemplateColumns: `repeat(${ preset.columns }, 1fr)`,
													gridTemplateRows: `repeat(${ preset.rows }, 1fr)`,
												} as CSSProperties
											}
										>
											{ preset.cells.map(
												( [ cs, ce, rs, re ], index ) => (
													<span
														key={ index }
														className="bento-preset-picker__cell"
														style={
															{
																gridColumn: `${ cs } / ${ ce }`,
																gridRow: `${ rs } / ${ re }`,
															} as CSSProperties
														}
													/>
												)
											) }
										</span>
										<span className="bento-pro-presets__label">
											{ preset.label }
										</span>
										<span
											className="bento-pro-presets__lock"
											aria-hidden="true"
										>
											🔒
										</span>
									</button>
								) ) }
							</div>
						</div>
					) }
				</PanelBody>

				<PanelBody title={ __( 'Spacing', 'bold-bento-grid' ) } initialOpen={ false }>
					<RangeControl
						label={ __( 'Gap', 'bold-bento-grid' ) }
						value={ gap }
						onChange={ ( value ) =>
							setAttributes( { gap: value ?? bentoGridDefaultAttributes.gap } )
						}
						min={ BENTO_MIN_GAP }
						max={ BENTO_MAX_GAP }
						step={ 1 }
					/>

					<RangeControl
						label={ __( 'Border Radius', 'bold-bento-grid' ) }
						value={ borderRadius }
						onChange={ ( value ) =>
							setAttributes( {
								borderRadius: value ?? bentoGridDefaultAttributes.borderRadius,
							} )
						}
						min={ BENTO_MIN_RADIUS }
						max={ BENTO_MAX_RADIUS }
						step={ 1 }
					/>
				</PanelBody>

				<PanelColorSettings
					title={ __( 'Color', 'bold-bento-grid' ) }
					colorSettings={ [
						{
							value: bgColor,
							onChange: ( value?: string ) =>
								setAttributes( {
									bgColor: value ?? bentoGridDefaultAttributes.bgColor,
								} ),
							label: __( 'Background', 'bold-bento-grid' ),
						},
					] }
				/>
			</InspectorControls>

			<div { ...blockProps }>
				<InnerBlocks
					allowedBlocks={ [ BENTO_TILE_BLOCK_NAME ] }
					template={ BENTO_TILE_TEMPLATE }
					templateLock={ false }
					renderAppender={ BentoNoAppender }
				/>
			</div>

			{ proModalOpen && (
				<Modal
					title={ __( 'Bento Grid Pro', 'bold-bento-grid' ) }
					onRequestClose={ () => setProModalOpen( false ) }
					className="bento-pro-modal"
				>
					<p className="bento-pro-modal__copy">{ BENTO_PRO_COPY }</p>

					<ul className="bento-pro-modal__list">
						<li>{ __( '6, 7 & 8-tile layouts', 'bold-bento-grid' ) }</li>
						<li>{ __( 'Glassmorphism & gradient presets', 'bold-bento-grid' ) }</li>
						<li>{ __( 'Dynamic WooCommerce / query product grids', 'bold-bento-grid' ) }</li>
						<li>{ __( 'Advanced hover & 3D animations', 'bold-bento-grid' ) }</li>
					</ul>

					<div className="bento-pro-modal__actions">
						<Button variant="primary" href={ proUrl } target="_blank" rel="noopener noreferrer">
							{ __( 'Explore Bento Grid Pro', 'bold-bento-grid' ) }
						</Button>
						<Button variant="tertiary" onClick={ () => setProModalOpen( false ) }>
							{ __( 'Maybe later', 'bold-bento-grid' ) }
						</Button>
					</div>
				</Modal>
			) }
		</>
	);
}

import type { CSSProperties } from 'react';
import { __ } from '@wordpress/i18n';
import {
	InspectorControls,
	InnerBlocks,
	useBlockProps,
} from '@wordpress/block-editor';
import {
	PanelBody,
	RangeControl,
	SelectControl,
	Button,
	ButtonGroup,
	Flex,
	FlexItem,
} from '@wordpress/components';
import type { BlockEditProps, TemplateArray } from '@wordpress/blocks';

import { bentoGridDefaultAttributes } from '../shared';

export interface BentoGridBlockAttributes {
	columns: number;
	gap: number;
	preset: string;
}

/**
 * Gap presets exposed in the editor. Values are stored in pixels on the
 * `gap` attribute and consumed by the shared SCSS via --bento-gap.
 */
const BENTO_GAP_PRESETS = [
	{ label: __( 'Compact', 'bento-grid' ), value: 8 },
	{ label: __( 'Comfortable', 'bento-grid' ), value: 16 },
	{ label: __( 'Spacious', 'bento-grid' ), value: 24 },
];

/**
 * Layout presets bundle a column count + gap combination behind a single
 * click, mirroring the quick-start presets common in Vercel/Stripe-style
 * builder UIs.
 */
const BENTO_LAYOUT_PRESETS: Array< {
	name: string;
	label: string;
	columns: number;
	gap: number;
} > = [
	{
		name: 'balanced',
		label: __( 'Balanced', 'bento-grid' ),
		columns: 3,
		gap: 16,
	},
	{
		name: 'featured',
		label: __( 'Featured', 'bento-grid' ),
		columns: 2,
		gap: 24,
	},
	{
		name: 'compact',
		label: __( 'Compact', 'bento-grid' ),
		columns: 4,
		gap: 8,
	},
];

/**
 * Default tile template: three Group blocks pre-classed as bento tiles,
 * each a free drop zone for images, text, or custom blocks.
 */
const BENTO_TILE_TEMPLATE: TemplateArray = [
	[
		'core/group',
		{ className: 'bento-grid__tile' },
		[
			[
				'core/paragraph',
				{
					placeholder: __( 'Drop an image, heading, or block…', 'bento-grid' ),
				},
			],
		],
	],
	[
		'core/group',
		{ className: 'bento-grid__tile' },
		[
			[
				'core/paragraph',
				{
					placeholder: __( 'Drop an image, heading, or block…', 'bento-grid' ),
				},
			],
		],
	],
	[
		'core/group',
		{ className: 'bento-grid__tile' },
		[
			[
				'core/paragraph',
				{
					placeholder: __( 'Drop an image, heading, or block…', 'bento-grid' ),
				},
			],
		],
	],
];

export default function BentoGridEdit( {
	attributes,
	setAttributes,
}: BlockEditProps< BentoGridBlockAttributes > ) {
	const {
		columns = bentoGridDefaultAttributes.columns,
		gap = bentoGridDefaultAttributes.gap,
		preset = 'balanced',
	} = attributes;

	const bentoApplyPreset = ( presetName: string ) => {
		const found = BENTO_LAYOUT_PRESETS.find( ( item ) => item.name === presetName );

		if ( ! found ) {
			return;
		}

		setAttributes( {
			preset: found.name,
			columns: found.columns,
			gap: found.gap,
		} );
	};

	const gridStyle: CSSProperties & Record< string, string | number > = {
		'--bento-columns': columns,
		'--bento-gap': `${ gap }px`,
	};

	const blockProps = useBlockProps( {
		className: 'bento-grid-wrapper',
	} );

	return (
		<>
			<InspectorControls>
				<PanelBody
					title={ __( 'Bento Settings', 'bento-grid' ) }
					initialOpen={ true }
				>
					<RangeControl
						label={ __( 'Columns', 'bento-grid' ) }
						help={ __( 'Number of columns on desktop. Collapses to a single column under 768px.', 'bento-grid' ) }
						value={ columns }
						onChange={ ( value ) =>
							setAttributes( { columns: value ?? bentoGridDefaultAttributes.columns } )
						}
						min={ 2 }
						max={ 4 }
						step={ 1 }
					/>

					<SelectControl
						label={ __( 'Gap', 'bento-grid' ) }
						value={ gap }
						options={ BENTO_GAP_PRESETS.map( ( item ) => ( {
							label: item.label,
							value: item.value,
						} ) ) }
						onChange={ ( value ) => setAttributes( { gap: Number( value ) } ) }
					/>

					<Flex direction="column" gap={ 2 } style={ { marginTop: '1rem' } }>
						<FlexItem>
							<label className="bento-preset-label">
								{ __( 'Layout presets', 'bento-grid' ) }
							</label>
						</FlexItem>
						<FlexItem>
							<ButtonGroup>
								{ BENTO_LAYOUT_PRESETS.map( ( item ) => (
									<Button
										key={ item.name }
										variant={ preset === item.name ? 'primary' : 'secondary' }
										onClick={ () => bentoApplyPreset( item.name ) }
									>
										{ item.label }
									</Button>
								) ) }
							</ButtonGroup>
						</FlexItem>
					</Flex>
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				<div className="bento-grid" style={ gridStyle }>
					<InnerBlocks
						template={ BENTO_TILE_TEMPLATE }
						templateLock={ false }
						renderAppender={ InnerBlocks.ButtonBlockAppender }
					/>
				</div>
			</div>
		</>
	);
}

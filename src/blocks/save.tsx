import { InnerBlocks, useBlockProps } from '@wordpress/block-editor';
import type { BlockSaveProps } from '@wordpress/blocks';

import { bentoGridDefaultAttributes } from '../shared';
import { bentoGridStyle } from './edit';
import type { BentoGridBlockAttributes } from './edit';

export default function BentoGridSave( { attributes }: BlockSaveProps< BentoGridBlockAttributes > ) {
	const {
		layoutPreset = bentoGridDefaultAttributes.layoutPreset,
		gap = bentoGridDefaultAttributes.gap,
		borderRadius = bentoGridDefaultAttributes.borderRadius,
		bgColor = bentoGridDefaultAttributes.bgColor,
	} = attributes;

	const blockProps = useBlockProps.save( {
		className: 'bold-bento-wrapper',
		'data-bento-grid': layoutPreset,
		style: bentoGridStyle( gap, borderRadius, bgColor ),
	} );

	return (
		<div { ...blockProps }>
			<InnerBlocks.Content />
		</div>
	);
}

import type { CSSProperties } from 'react';
import { InnerBlocks, useBlockProps } from '@wordpress/block-editor';
import type { BlockSaveProps } from '@wordpress/blocks';

import { bentoGridDefaultAttributes } from '../shared';
import type { BentoGridBlockAttributes } from './edit';

export default function BentoGridSave( {
	attributes,
}: BlockSaveProps< BentoGridBlockAttributes > ) {
	const {
		columns = bentoGridDefaultAttributes.columns,
		gap = bentoGridDefaultAttributes.gap,
	} = attributes;

	const gridStyle: CSSProperties & Record< string, string | number > = {
		'--bento-columns': columns,
		'--bento-gap': `${ gap }px`,
	};

	const blockProps = useBlockProps.save( {
		className: 'bento-grid-wrapper',
		'data-bento-grid-columns': columns,
		'data-bento-grid-gap': gap,
	} );

	return (
		<div { ...blockProps }>
			<div className="bento-grid" style={ gridStyle }>
				<InnerBlocks.Content />
			</div>
		</div>
	);
}

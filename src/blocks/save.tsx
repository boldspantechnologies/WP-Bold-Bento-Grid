import type { CSSProperties } from 'react';
import { InnerBlocks, useBlockProps } from '@wordpress/block-editor';
import type { BlockSaveProps } from '@wordpress/blocks';

import { bentoGridDefaultAttributes } from '../shared';
import type { BentoGridBlockAttributes } from './edit';

/**
 * Static save output for the Bento Grid block.
 *
 * Produces the exact `.bento-grid-wrapper > .bento-grid` structure (with
 * `--bento-columns` / `--bento-gap` custom properties) that the shared
 * runtime engine (src/shared/scripts/bento-runtime.ts) and SCSS layout
 * engine (src/shared/styles/bento-grid.scss) target, and that the
 * Elementor widget's render() must reproduce byte-for-byte per
 * AGENT.md 7 (single source of truth for frontend DOM/CSS).
 */
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

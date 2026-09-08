import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';

import metadata from '../../block.json';
import { bentoGridDefaultAttributes } from '../shared';

import './style.scss';

interface BentoGridBlockAttributes {
	columns: number;
	gap: number;
}

registerBlockType< BentoGridBlockAttributes >( metadata.name, {
	edit: ( { attributes, setAttributes } ) => {
		const { columns = bentoGridDefaultAttributes.columns } = attributes;
		const blockProps = useBlockProps( {
			className: 'bento-grid',
			'data-bento-grid-columns': columns,
		} );

		return <div { ...blockProps }>Bento Grid</div>;
	},
	save: ( { attributes } ) => {
		const { columns = bentoGridDefaultAttributes.columns } = attributes;
		const blockProps = useBlockProps.save( {
			className: 'bento-grid',
			'data-bento-grid-columns': columns,
		} );

		return <div { ...blockProps } />;
	},
} );

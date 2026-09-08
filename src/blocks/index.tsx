import { registerBlockType } from '@wordpress/blocks';

import metadata from '../../block.json';
import BentoGridEdit, { BentoGridBlockAttributes } from './edit';
import BentoGridSave from './save';

import './style.scss';

registerBlockType< BentoGridBlockAttributes >( metadata.name, {
	edit: BentoGridEdit,
	save: BentoGridSave,
} );

import { registerBlockType } from '@wordpress/blocks';
import type { BlockConfiguration } from '@wordpress/blocks';

import metadata from './block.json';
import BentoGridEdit, { BentoGridBlockAttributes } from './edit';
import BentoGridSave from './save';
import './tile';

import './style.scss';

registerBlockType< BentoGridBlockAttributes >(
	metadata as unknown as BlockConfiguration< BentoGridBlockAttributes >,
	{
		edit: BentoGridEdit,
		save: BentoGridSave,
	}
);

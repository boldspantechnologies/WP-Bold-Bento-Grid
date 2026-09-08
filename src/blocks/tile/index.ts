import { registerBlockType } from '@wordpress/blocks';
import type { BlockConfiguration } from '@wordpress/blocks';

import metadata from './block.json';
import BentoTileEdit, { BentoTileAttributes } from './edit';
import BentoTileSave from './save';

registerBlockType< BentoTileAttributes >(
	metadata as unknown as BlockConfiguration< BentoTileAttributes >,
	{
		edit: BentoTileEdit,
		save: BentoTileSave,
	}
);

import './scripts/bento-runtime';

export const BENTO_GRID_ATTR_PREFIX = 'data-bento-grid';

export interface BentoGridAttributes {
	layoutPreset: string;
	tileCount: number;
	gap: number;
	borderRadius: number;
	bgColor: string;
}

export const bentoGridDefaultAttributes: BentoGridAttributes = {
	layoutPreset: 'hero-3',
	tileCount: 3,
	gap: 16,
	borderRadius: 16,
	bgColor: '#f4f4f5',
};

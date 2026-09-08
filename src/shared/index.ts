import './scripts/bento-runtime';

export const BENTO_GRID_ATTR_PREFIX = 'data-bento-grid';

export interface BentoGridAttributes {
	columns: number;
	gap: number;
}

export const bentoGridDefaultAttributes: BentoGridAttributes = {
	columns: 3,
	gap: 16,
};

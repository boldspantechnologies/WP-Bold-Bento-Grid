/**
 * Shared types and constants used by both the Gutenberg block and the
 * Elementor widget, keeping their HTML output/data-* attributes in sync.
 */

export const BENTO_GRID_ATTR_PREFIX = 'data-bento-grid';

export interface BentoGridAttributes {
	columns: number;
	gap: number;
}

export const bentoGridDefaultAttributes: BentoGridAttributes = {
	columns: 3,
	gap: 16,
};

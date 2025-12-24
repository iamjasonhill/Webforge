/** @type {import('tailwindcss').Config} */
export default {
	content: ['./src/**/*.{astro,html,js,jsx,md,mdx,svelte,ts,tsx,vue}'],
	theme: {
		extend: {
			colors: {
				brand: {
					red: '#4f46e5',    // Default: Indigo-600
					yellow: '#facc15', // Default: Yellow-400
					accent: '#ec4899', // Default: Pink-500
					dark: '#0f172a',   // Default: Slate-900
					light: '#f8fafc',  // Default: Slate-50
					gray: '#334155',   // Default: Slate-700
				}
			}
		},
	},
	plugins: [],
}

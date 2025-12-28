/** @type {import('tailwindcss').Config} */
export default {
	content: ['./src/**/*.{astro,html,js,jsx,md,mdx,svelte,ts,tsx,vue}'],
	theme: {
		extend: {
			colors: {
				brand: {
					primary: '#1E40AF',   // Deep Blue (Trust/Security)
					secondary: '#059669', // Emerald Green (Safety/Growth)
					accent: '#F59E0B',    // Amber (Attention/CTA)
					dark: '#1F2937',      // Dark Gray (Text)
					light: '#F9FAFB',     // Light Gray (Backgrounds)
					gray: '#6B7280',      // Neutral Gray
				}
			},
			fontFamily: {
				sans: ['Inter', 'system-ui', 'sans-serif'],
				heading: ['Poppins', 'Inter', 'sans-serif'],
			},
			borderRadius: {
				'brand': '12px',
			},
			backgroundImage: {
				'hero-gradient': 'linear-gradient(135deg, #1E40AF 0%, #3B82F6 50%, #059669 100%)',
			}
		},
	},
	plugins: [],
}


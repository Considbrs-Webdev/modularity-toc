import { createViteConfig } from "vite-config-factory";

const entries = {
        'css/modularity-toc':                           './source/sass/modularity-toc.scss',
        'css/modularity-toc-breakpoint-override':       './source/sass/modularity-toc-breakpoint-override.scss',
        'js/modularity-toc':                            './source/js/modularity-toc.js',
};

export default createViteConfig(entries, {
	outDir: "assets/dist",
	manifestFile: "manifest.json",
});

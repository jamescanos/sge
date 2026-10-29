import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// Gráficos del Dashboard: Chart.js solo se descarga en las páginas que tienen gráficos
if (document.querySelector('canvas[data-chart]')) {
    import('./charts').then(({ renderCharts }) => renderCharts());
}

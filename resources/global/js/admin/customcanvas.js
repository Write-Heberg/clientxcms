import Chart from 'chart.js/auto';
import {Tooltip} from 'chart.js';
Chart.register(Tooltip);

class CustomCanvas extends HTMLCanvasElement {

    connectedCallback() {
        this.addStyle()
        this.labels = this.hasData('labels', true)
        this.set = this.hasData('set', true)
        this.backgrounds = this.hasData('backgrounds', true)
        this.type = this.hasData('type', false, "pie")
        this.suffix = this.hasData('suffix', false, "")
        this.max = this.hasData('max', false, 0)
        this.makeChart()
    }

    addStyle() {
        this.style.minHeight = "250px";
        this.style.height = "250px";
        this.style.maxHeight = "250px";
        this.style.maxWidth = "100%";
    }

    makeChart() {
        const self = this;
        let datasets = [{
            data: this.set,
            label: this.title,
            backgroundColor: this.backgrounds,
        }]
        const data = {
            labels: this.labels,
            datasets: datasets,
        }
        let scaleY = {
            beginAtZero: true,
            callback: function(value, index, values) {
                return value + self.suffix;
            }
        }
        if (this.max !== 0) {
            scaleY.max = this.max
        }
        let config = {
            type: this.type,
            data: data,
            options: {
                maintainAspectRatio: false,
                responsive: true,
                plugins: {},
            }
        }
        if (this.type === "line") {
            config.options.fill = true
            config.options.plugins.tooltip = {
                mode: 'index',
                intersect: false,
                callbacks: {
                    label: function(context) {
                        let label = context.dataset.label || '';
                        if (label) {
                            label += ': ';
                        }
                        if (context.parsed.y !== null) {
                            label += context.parsed.y + self.suffix;
                        }
                        return label;
                    },
                    title: function(context) {
                        return context[0].label + ' - ' + context[0].formattedValue + self.suffix;
                    }
                }
            }
            config.scale = {
                y: scaleY,
                yAxes: [{
                    ticks: {
                        beginAtZero:true,
                        callback: function(value, index, values) {
                            return value + self.suffix;
                        }
                    }
                }]
            }
        }
        new Chart(this, config);
    }

    hasData(keyName, json = false, defaultValue = {}) {
        if (this.dataset[keyName] !== undefined) {
            if (json === false) {
                return this.dataset[keyName]
            }
            return JSON.parse(this.dataset[keyName])

        }
        return defaultValue
    }
}

customElements.define("custom-canvas", CustomCanvas, { extends: 'canvas' })

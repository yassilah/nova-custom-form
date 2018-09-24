Nova.booting((Vue, router) => {
    Vue.component('{{ component }}-create', require('./components/Create'))
    Vue.component('{{ component }}-edit', require('./components/Edit'))
})
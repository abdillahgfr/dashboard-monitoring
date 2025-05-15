<template>
    <section role="main" class="content-body">
        <header class="page-header">
            <h2>Monitoring Persediaan</h2>
        </header>

        <!-- Replace this with reusable components and props as needed -->
        <div class="row">
            <div
                class="col-xl-4"
                v-for="(data, index) in chartData"
                :key="index"
            >
                <div class="card card-primary mb-4">
                    <div class="card-header">
                        <h2 class="card-title">{{ data.title }}</h2>
                        <p class="card-subtitle">{{ data.subtitle }}</p>
                    </div>
                    <div class="card-body">
                        <div class="chart chart-md" :id="data.chartId"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Section -->
        <div class="card mt-4">
            <div class="card-header">
                <h2 class="card-title">Tabel Monitoring Persediaan</h2>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>KOLOK</th>
                            <th>Flag</th>
                            <th>NALOK</th>
                            <th>Tahun</th>
                            <th>Notifikasi</th>
                            <th>Stok Opname</th>
                            <th>BA Stok Fisik</th>
                            <th>Rekon BKU</th>
                            <th>Progress</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in mergedData" :key="item.id_kolok">
                            <td>{{ item.id_kolok }}</td>
                            <td>{{ item.flag }}</td>
                            <td>{{ item.nalok }}</td>
                            <td>{{ item.tahun }}</td>
                            <td>{{ item.Total_SPPB_BAST }}</td>
                            <td>
                                <span :class="item.periode_baso_badge">{{
                                    item.periode_baso_status
                                }}</span>
                            </td>
                            <td>
                                <span :class="item.tglba_fisik_badge">{{
                                    item.tglba_fisik_status
                                }}</span>
                            </td>
                            <td>
                                Sudah: {{ item.rekon_sudah }} <br />
                                Belum: {{ item.rekon_belum }} <br />
                                Status: <strong>{{ item.rekon_status }}</strong>
                            </td>
                            <td>
                                <div
                                    class="progress progress-sm progress-half-rounded"
                                >
                                    <div
                                        class="progress-bar"
                                        :class="item.progress_class"
                                        :style="`width: ${item.progress}%`"
                                    >
                                        {{ item.progress }}%
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</template>

<script>
export default {
    props: ["mergedData", "chartData"],
    mounted() {
        // Call chart library rendering here (e.g., Chart.js, Flot, etc.)
        // Example:
        this.drawCharts();
    },
    methods: {
        drawCharts() {
            this.chartData.forEach((chart) => {
                // Custom logic to draw each pie chart using flot or chart.js
            });
        },
    },
};
</script>

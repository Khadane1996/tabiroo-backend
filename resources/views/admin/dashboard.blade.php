@extends('admin.layouts.app')

@section('page-title', 'Dashboard Tabiroo')

@section('content')

    {{-- Articles blog (existant) --}}
    <h5 class="mb-3">Blog</h5>
    <div class="row">
        <div class="col-xxl-4 col-md-6 mb-3">
            <div class="card info-card">
                <div class="card-body">
                    <h5 class="card-title">Articles de blog</h5>
                    <div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                            <i class="bi bi-newspaper"></i>
                        </div>
                        <div class="ps-3">
                            <h6>{{ $postsCount }}</h6>
                            <span class="text-muted small pt-2 ps-1">articles au total</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl-4 col-md-6 mb-3">
            <div class="card info-card">
                <div class="card-body">
                    <h5 class="card-title">Articles publiés</h5>
                    <div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                            <i class="bi bi-check-circle"></i>
                        </div>
                        <div class="ps-3">
                            <h6>{{ $publishedCount }}</h6>
                            <span class="text-muted small pt-2 ps-1">visibles sur le site</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- Indicateurs clés --}}
    <h5 class="mb-3">Indicateurs clés</h5>
    <div class="row mb-4">
        <div class="col-xxl-4 col-md-6 mb-3">
            <div class="card info-card">
                <div class="card-body">
                    <h5 class="card-title">Réservations</h5>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6>{{ $reservationsToday ?? 0 }}</h6>
                            <span class="text-muted small">Aujourd'hui</span>
                        </div>
                        <div class="text-end">
                            <h6>{{ $reservationsThisMonth ?? 0 }}</h6>
                            <span class="text-muted small">Ce mois-ci</span>
                        </div>
                    </div>
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center mt-2">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl-4 col-md-6 mb-3">
            <div class="card info-card">
                <div class="card-body">
                    <h5 class="card-title">Convives accueillis</h5>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6>{{ $convivesToday ?? 0 }}</h6>
                            <span class="text-muted small">Aujourd'hui</span>
                        </div>
                        <div class="text-end">
                            <h6>{{ $convivesThisMonth ?? 0 }}</h6>
                            <span class="text-muted small">Ce mois-ci</span>
                        </div>
                    </div>
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center mt-2">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl-4 col-md-6 mb-3">
            <div class="card info-card">
                <div class="card-body">
                    <h5 class="card-title">Chiffre d'affaires brut</h5>
                    <div>
                        <h6>{{ number_format($caMoisEnCours ?? 0, 0, ',', ' ') }} €</h6>
                        <span class="text-muted small">Mois en cours</span>
                    </div>
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center mt-2">
                        <i class="bi bi-currency-euro"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl-4 col-md-6 mb-3">
            <div class="card info-card">
                <div class="card-body">
                    <h5 class="card-title">Prestations à venir</h5>
                    <div>
                        <h6>{{ $prestationsAVenir ?? 0 }}</h6>
                    </div>
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center mt-2">
                        <i class="bi bi-arrow-right-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Activité plateforme --}}
    <h5 class="mb-3">Activité plateforme</h5>
    <div class="row mb-4">
        <div class="col-xxl-4 col-md-6 mb-3">
            <div class="card info-card">
                <div class="card-body">
                    <h5 class="card-title">Hôtes actifs</h5>
                    <h6>{{ $hotesActifs ?? 0 }}</h6>
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center mt-2">
                        <i class="bi bi-house-door"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl-4 col-md-6 mb-3">
            <div class="card info-card">
                <div class="card-body">
                    <h5 class="card-title">Convives actifs</h5>
                    <h6>{{ $convivesActifs ?? 0 }}</h6>
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center mt-2">
                        <i class="bi bi-person-check"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl-4 col-md-6 mb-3">
            <div class="card info-card">
                <div class="card-body">
                    <h5 class="card-title">Prestations publiées</h5>
                    <h6>{{ $prestationsPubliees ?? 0 }}</h6>
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center mt-2">
                        <i class="bi bi-megaphone"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Graphique Convives par jour --}}
    <h5 class="mb-3">Tendance — Convives accueillis par jour (30 derniers jours)</h5>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <canvas id="convivesChart" height="80"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Annulations --}}
    <h5 class="mb-3">Statistiques des annulations</h5>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted small mb-3">Avant / Après 48h avant la prestation</p>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th class="text-center">Convive (annulation)</th>
                                    <th class="text-center">Hôte (annulation)</th>
                                    <th class="text-center">Autre / système</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Avant 48h</strong></td>
                                    <td class="text-center">{{ $annulations['convive_avant_48h'] ?? 0 }}</td>
                                    <td class="text-center">{{ $annulations['hote_avant_48h'] ?? 0 }}</td>
                                    <td class="text-center">{{ $annulations['autre_avant_48h'] ?? 0 }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Après 48h</strong></td>
                                    <td class="text-center">{{ $annulations['convive_apres_48h'] ?? 0 }}</td>
                                    <td class="text-center">{{ $annulations['hote_apres_48h'] ?? 0 }}</td>
                                    <td class="text-center">{{ $annulations['autre_apres_48h'] ?? 0 }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('admin-assets/vendor/chart.js/chart.umd.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('convivesChart');
    if (!ctx) return;
    const data = @json($convivesParJour ?? []);
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.map(d => d.label),
            datasets: [{
                label: 'Convives accueillis',
                data: data.map(d => d.count),
                backgroundColor: 'rgba(221, 102, 0, 0.6)',
                borderColor: 'rgba(221, 102, 0, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 }
                }
            },
            plugins: {
                legend: { display: false }
            }
        }
    });
});
</script>
@endpush

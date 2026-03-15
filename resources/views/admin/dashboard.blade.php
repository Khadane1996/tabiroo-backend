@extends('admin.layouts.app')

@section('page-title', 'Dashboard Tabiroo')

@section('content')

    {{-- Filtre par période + Export CSV --}}
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div class="btn-group" role="group">
                <a href="{{ route('admin.dashboard', ['period' => 'day']) }}"
                   class="btn btn-sm {{ ($period ?? 'month') === 'day' ? 'btn-primary' : 'btn-outline-primary' }}">
                    Aujourd'hui
                </a>
                <a href="{{ route('admin.dashboard', ['period' => 'week']) }}"
                   class="btn btn-sm {{ ($period ?? 'month') === 'week' ? 'btn-primary' : 'btn-outline-primary' }}">
                    Cette semaine
                </a>
                <a href="{{ route('admin.dashboard', ['period' => 'month']) }}"
                   class="btn btn-sm {{ ($period ?? 'month') === 'month' ? 'btn-primary' : 'btn-outline-primary' }}">
                    Ce mois
                </a>
                <a href="{{ route('admin.dashboard', ['period' => 'all']) }}"
                   class="btn btn-sm {{ ($period ?? 'month') === 'all' ? 'btn-primary' : 'btn-outline-primary' }}">
                    Tout
                </a>
            </div>
            <a href="{{ route('admin.dashboard.export-csv', ['period' => $period ?? 'month']) }}"
               class="btn btn-sm btn-success">
                <i class="bi bi-download"></i> Export CSV
            </a>
        </div>
    </div>

    {{-- ========== SECTION 1: Volume d'activité ========== --}}
    <h5 class="mb-3"><i class="bi bi-bar-chart"></i> Volume d'activité</h5>
    <div class="row mb-4">
        <div class="col-xxl-4 col-md-6 mb-3">
            <div class="card info-card">
                <div class="card-body">
                    <h5 class="card-title">Total réservations</h5>
                    <h6>{{ number_format($volumeStats['total_reservations'] ?? 0) }}</h6>
                </div>
            </div>
        </div>
        <div class="col-xxl-4 col-md-6 mb-3">
            <div class="card info-card">
                <div class="card-body">
                    <h5 class="card-title">Montant menus (sous-total)</h5>
                    <h6>{{ number_format($volumeStats['total_menus_amount'] ?? 0, 2, ',', ' ') }} &euro;</h6>
                </div>
            </div>
        </div>
        <div class="col-xxl-4 col-md-6 mb-3">
            <div class="card info-card">
                <div class="card-body">
                    <h5 class="card-title">Total payé par convives</h5>
                    <h6>{{ number_format($volumeStats['total_paid_by_guests'] ?? 0, 2, ',', ' ') }} &euro;</h6>
                </div>
            </div>
        </div>
        <div class="col-xxl-4 col-md-6 mb-3">
            <div class="card info-card">
                <div class="card-body">
                    <h5 class="card-title">Reversé aux hôtes</h5>
                    <h6>{{ number_format($volumeStats['total_reversed_to_hosts'] ?? 0, 2, ',', ' ') }} &euro;</h6>
                </div>
            </div>
        </div>
        <div class="col-xxl-4 col-md-6 mb-3">
            <div class="card info-card">
                <div class="card-body">
                    <h5 class="card-title">Commissions Tabiroo</h5>
                    <h6>{{ number_format($volumeStats['total_commissions'] ?? 0, 2, ',', ' ') }} &euro;</h6>
                </div>
            </div>
        </div>
        <div class="col-xxl-4 col-md-6 mb-3">
            <div class="card info-card">
                <div class="card-body">
                    <h5 class="card-title">Frais Stripe</h5>
                    <h6>{{ number_format($volumeStats['total_payment_fees'] ?? 0, 2, ',', ' ') }} &euro;</h6>
                </div>
            </div>
        </div>
    </div>

    {{-- ========== SECTION 2: Réservations Auto vs Manuelles ========== --}}
    <h5 class="mb-3"><i class="bi bi-arrow-left-right"></i> Réservations automatiques vs manuelles</h5>
    <div class="row mb-4">
        <div class="col-md-6 mb-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Flux automatique</h5>
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6>{{ $flowStats['automatic_count'] ?? 0 }}</h6>
                            <span class="text-muted small">réservations</span>
                        </div>
                        <div class="text-end">
                            <h6>{{ number_format($flowStats['automatic_amount'] ?? 0, 2, ',', ' ') }} &euro;</h6>
                            <span class="text-muted small">montant total</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Flux manuel (validation hôte)</h5>
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6>{{ $flowStats['manual_count'] ?? 0 }}</h6>
                            <span class="text-muted small">réservations</span>
                        </div>
                        <div class="text-end">
                            <h6>{{ number_format($flowStats['manual_amount'] ?? 0, 2, ',', ' ') }} &euro;</h6>
                            <span class="text-muted small">montant total</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">Taux d'acceptation (manuel)</h5>
                    <h3 class="{{ ($flowStats['acceptance_rate'] ?? 0) >= 80 ? 'text-success' : 'text-warning' }}">
                        {{ $flowStats['acceptance_rate'] ?? 0 }}%
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">Refusées par hôte</h5>
                    <h3 class="text-danger">{{ $flowStats['declined_count'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">Expirées (sans réponse)</h5>
                    <h3 class="text-secondary">{{ $flowStats['expired_count'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- ========== SECTION 3: Annulations ========== --}}
    <h5 class="mb-3"><i class="bi bi-x-circle"></i> Annulations</h5>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted small">Détail par type d'annulation</span>
                        <span class="badge bg-danger">Taux : {{ $annulations['cancellation_rate'] ?? 0 }}%</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Type</th>
                                    <th class="text-center">Nombre</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Convive — avant 48h (remboursement total)</td>
                                    <td class="text-center">{{ $annulations['guest_before_48h'] ?? 0 }}</td>
                                </tr>
                                <tr>
                                    <td>Convive — après 48h (pas de remboursement)</td>
                                    <td class="text-center">{{ $annulations['guest_after_48h'] ?? 0 }}</td>
                                </tr>
                                <tr>
                                    <td>Hôte (remboursement total systématique)</td>
                                    <td class="text-center">{{ $annulations['by_host'] ?? 0 }}</td>
                                </tr>
                                <tr>
                                    <td>Sans réponse hôte (expirée)</td>
                                    <td class="text-center">{{ $annulations['no_response'] ?? 0 }}</td>
                                </tr>
                                <tr class="table-warning">
                                    <td><strong>Total annulées</strong></td>
                                    <td class="text-center"><strong>{{ $annulations['total_cancelled'] ?? 0 }}</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    @if(($annulations['host_refunded_amount'] ?? 0) > 0)
                        <div class="alert alert-info mt-2 mb-0">
                            Montant remboursé suite aux annulations hôte : <strong>{{ number_format($annulations['host_refunded_amount'], 2, ',', ' ') }} &euro;</strong>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ========== SECTION 4: Remboursements ========== --}}
    <h5 class="mb-3"><i class="bi bi-arrow-counterclockwise"></i> Remboursements</h5>
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">Total remboursés</h5>
                    <h3>{{ $refundStats['total_refunds'] ?? 0 }}</h3>
                    <span class="text-muted small">{{ number_format($refundStats['total_refunded_amount'] ?? 0, 2, ',', ' ') }} &euro;</span>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">Par annulation hôte</h5>
                    <h6>{{ number_format($refundStats['refunded_by_host'] ?? 0, 2, ',', ' ') }} &euro;</h6>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">Par annulation convive</h5>
                    <h6>{{ number_format($refundStats['refunded_by_guest'] ?? 0, 2, ',', ' ') }} &euro;</h6>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">Remboursements en attente</h5>
                    <h3 class="{{ ($refundStats['pending_refunds'] ?? 0) > 0 ? 'text-warning' : '' }}">
                        {{ $refundStats['pending_refunds'] ?? 0 }}
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="card {{ ($refundStats['refund_failed_count'] ?? 0) > 0 ? 'border-danger' : '' }}">
                <div class="card-body text-center">
                    <h5 class="card-title text-danger">Remboursements échoués</h5>
                    <h3 class="text-danger">{{ $refundStats['refund_failed_count'] ?? 0 }}</h3>
                    @if(!empty($refundStats['refund_failed_reservations']))
                        <div class="mt-2">
                            <span class="text-muted small">Réservations : </span>
                            @foreach($refundStats['refund_failed_reservations'] as $resId)
                                <span class="badge bg-danger">#{{ $resId }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ========== SECTION 5: Paiements en attente ========== --}}
    <h5 class="mb-3"><i class="bi bi-hourglass-split"></i> Paiements en attente</h5>
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">En attente OTP</h5>
                    <h3>{{ $pendingPayments['pending_otp_count'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card {{ ($pendingPayments['expired_otp_count'] ?? 0) > 0 ? 'border-warning' : '' }}">
                <div class="card-body text-center">
                    <h5 class="card-title">OTP expirés</h5>
                    <h3 class="text-warning">{{ $pendingPayments['expired_otp_count'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">Payout en attente</h5>
                    <h3>{{ $pendingPayments['pending_payout_count'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card {{ ($pendingPayments['captured_not_transferred'] ?? 0) > 0 ? 'border-info' : '' }}">
                <div class="card-body text-center">
                    <h5 class="card-title">Capturés non transférés</h5>
                    <h3 class="text-info">{{ $pendingPayments['captured_not_transferred'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- ========== SECTION 6: Comptes Stripe hôtes ========== --}}
    <h5 class="mb-3"><i class="bi bi-credit-card"></i> Comptes Stripe des hôtes</h5>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Statut</th>
                                    <th class="text-center">Nombre</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><span class="badge bg-success">Activé</span> Payouts actifs</td>
                                    <td class="text-center">{{ $stripeAccountStats['activated'] ?? 0 }}</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-warning">En cours</span> Vérification en attente</td>
                                    <td class="text-center">{{ $stripeAccountStats['pending_verification'] ?? 0 }}</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-danger">Action requise</span> Documents manquants</td>
                                    <td class="text-center">{{ $stripeAccountStats['action_required'] ?? 0 }}</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-secondary">Non démarré</span> Pas de compte Stripe</td>
                                    <td class="text-center">{{ $stripeAccountStats['not_started'] ?? 0 }}</td>
                                </tr>
                                <tr class="table-light">
                                    <td><strong>Total comptes créés</strong></td>
                                    <td class="text-center"><strong>{{ $stripeAccountStats['total_created'] ?? 0 }}</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ========== SECTION 7: Incidents ========== --}}
    <h5 class="mb-3"><i class="bi bi-exclamation-triangle"></i> Incidents</h5>
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card {{ ($incidentStats['open'] ?? 0) > 0 ? 'border-danger' : '' }}">
                <div class="card-body text-center">
                    <h5 class="card-title">Ouverts</h5>
                    <h3 class="text-danger">{{ $incidentStats['open'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">Résolus</h5>
                    <h3 class="text-success">{{ $incidentStats['resolved'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">Contestations (disputes)</h5>
                    <h3>{{ $incidentStats['disputes'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">Échecs remboursement</h5>
                    <h3>{{ $incidentStats['refund_failures'] ?? 0 }}</h3>
                </div>
            </div>
        </div>

        @if(!empty($incidentStats['recent_incidents']) && count($incidentStats['recent_incidents']) > 0)
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">10 derniers incidents ouverts</h5>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Type</th>
                                        <th>Réservation</th>
                                        <th>Description</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($incidentStats['recent_incidents'] as $incident)
                                        <tr>
                                            <td>{{ $incident->id }}</td>
                                            <td>
                                                @if($incident->type === 'dispute')
                                                    <span class="badge bg-danger">Contestation</span>
                                                @elseif($incident->type === 'refund_failed')
                                                    <span class="badge bg-warning">Remb. échoué</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ $incident->type }}</span>
                                                @endif
                                            </td>
                                            <td>#{{ $incident->reservation_id }}</td>
                                            <td>{{ \Illuminate\Support\Str::limit($incident->description, 60) }}</td>
                                            <td>{{ $incident->created_at->format('d/m/Y H:i') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- ========== SECTION 8: Contestations bancaires (CDC 11.5) ========== --}}
    <h5 class="mb-3"><i class="bi bi-shield-exclamation"></i> Contestations bancaires (Disputes Stripe)</h5>
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">Total contestations</h5>
                    <h3>{{ $disputeStats['dispute_count'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card {{ ($disputeStats['dispute_open'] ?? 0) > 0 ? 'border-danger' : '' }}">
                <div class="card-body text-center">
                    <h5 class="card-title">En cours</h5>
                    <h3 class="text-danger">{{ $disputeStats['dispute_open'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">Montant contesté</h5>
                    <h6>{{ number_format($disputeStats['dispute_amount'] ?? 0, 2, ',', ' ') }} &euro;</h6>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">Taux de contestation</h5>
                    <h3 class="{{ ($disputeStats['dispute_rate'] ?? 0) > 1 ? 'text-danger' : 'text-success' }}">
                        {{ $disputeStats['dispute_rate'] ?? 0 }}%
                    </h3>
                </div>
            </div>
        </div>
    </div>

    <hr>

    {{-- ========== Indicateurs existants ========== --}}
    <h5 class="mb-3"><i class="bi bi-speedometer2"></i> Indicateurs clés</h5>
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
                    <h5 class="card-title">CA brut (mois en cours)</h5>
                    <h6>{{ number_format($caMoisEnCours ?? 0, 0, ',', ' ') }} &euro;</h6>
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
                    <h6>{{ $prestationsAVenir ?? 0 }}</h6>
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center mt-2">
                        <i class="bi bi-arrow-right-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Activité plateforme --}}
    <h5 class="mb-3"><i class="bi bi-activity"></i> Activité plateforme</h5>
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

    {{-- Articles blog --}}
    <h5 class="mb-3"><i class="bi bi-newspaper"></i> Blog</h5>
    <div class="row mb-4">
        <div class="col-xxl-4 col-md-6 mb-3">
            <div class="card info-card">
                <div class="card-body">
                    <h5 class="card-title">Articles de blog</h5>
                    <h6>{{ $postsCount }}</h6>
                    <span class="text-muted small">articles au total</span>
                </div>
            </div>
        </div>
        <div class="col-xxl-4 col-md-6 mb-3">
            <div class="card info-card">
                <div class="card-body">
                    <h5 class="card-title">Articles publiés</h5>
                    <h6>{{ $publishedCount }}</h6>
                    <span class="text-muted small">visibles sur le site</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Graphique Convives par jour --}}
    <h5 class="mb-3"><i class="bi bi-graph-up"></i> Tendance — Convives accueillis par jour (30 derniers jours)</h5>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <canvas id="convivesChart" height="80"></canvas>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('convivesChart');
    if (!ctx || typeof Chart === 'undefined') return;
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

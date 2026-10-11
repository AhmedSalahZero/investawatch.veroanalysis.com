<template>
  <Head title="Portfolio Dashboard" />
  <AuthenticatedLayout>
    <div class="min-h-screen bg-mp-page text-mp-text-secondary">

      <!-- ══ HEADER ══ -->
      <div class="bg-mp-card border-b border-mp-border">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
              <p class="text-xs font-semibold text-white uppercase tracking-widest mb-1">
                {{ org.name }}
              </p>
              <h1 class="text-2xl font-bold text-mp-text-secondary">Portfolio Dashboard</h1>
              <p class="text-mp-muted text-sm mt-1">
                Command center — {{ summary.total_companies }} companies · as of {{ today }}
              </p>

              <!-- Super-admin org switcher -->
              <div v-if="allOrgs.length > 1" class="flex items-center gap-2 mt-3">
                <span class="text-xs text-mp-muted">Viewing org:</span>
                <select
                  :value="currentOrgId"
                  @change="e => router.get('/dashboard', { org_id: e.target.value })"
                  class="bg-mp-card-hover border border-mp-border text-mp-text-secondary text-sm rounded-lg px-3 py-1.5 focus:outline-none focus:border-mp-teal cursor-pointer"
                >
                  <option v-for="o in allOrgs" :key="o.id" :value="o.id">{{ o.name }}</option>
                </select>
              </div>
            </div>
            <div class="flex items-center gap-3 flex-wrap">
              <Link
                href="/portfolio-companies/create"
                class="flex items-center gap-2 bg-mp-teal hover:bg-mp-teal-dark text-mp-text-secondary text-sm font-medium px-4 py-2 rounded-lg transition-colors"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Company
              </Link>
              <Link
                href="/portfolio-companies"
                class="flex items-center gap-2 bg-mp-card-hover hover:bg-mp-page border border-mp-border text-mp-text text-sm font-medium px-4 py-2 rounded-lg transition-colors"
              >
                All Companies
              </Link>
              <Link
                :href="safeOrgId ? `/organizations/${safeOrgId}/statistica` : '#'"
                class="flex items-center gap-2 bg-mp-success/60 hover:bg-mp-success/70 border border-mp-success/60 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                <span>📊</span>
                Statistica
              </Link>
              <Link
                href="/investor-decision"
                class="flex items-center gap-2 bg-mp-gold/60 hover:bg-mp-gold/70 border border-mp-gold/60 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors"
              >
                <span>⚖️</span>
                Investor Decision Tool
              </Link>

                  <!-- ── InvestaDocs button ── -->
            <Link
              :href="safeOrgId ? `/organizations/${safeOrgId}/investadocs` : '#'"
              class="flex items-center gap-2 bg-mp-teal-subtle/60 hover:bg-mp-teal-subtle/70 border border-mp-teal/60 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
              📝 InvestaDocs
            </Link>
            </div>
          </div>

          <!-- Tab nav -->
          <div class="flex gap-1 mt-5 border-b border-mp-border -mb-[1px]">
            <button
              v-for="tab in tabs" :key="tab.key"
              @click="activeTab = tab.key"
              :class="[
                'px-4 py-2.5 text-sm font-medium rounded-t-lg transition-colors border-b-2',
                activeTab === tab.key
                  ? 'text-mp-text-secondary border-mp-teal bg-mp-card-hover/60'
                  : 'text-mp-muted border-transparent hover:text-mp-text hover:bg-mp-card-hover/30'
              ]"
            >
              {{ tab.label }}
              <span v-if="tab.key === 'redflags' && summary.at_risk_count > 0"
                class="ml-1.5 bg-mp-danger text-mp-text-secondary text-xs font-bold px-1.5 py-0.5 rounded-full">
                {{ summary.at_risk_count }}
              </span>
            </button>
          </div>
        </div>
      </div>

      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

        <!-- ══════════════════════════════════════════
             TAB: OVERVIEW
        ═══════════════════════════════════════════ -->
        <div v-if="activeTab === 'overview'">

          <!-- SECTION 1 — Headline KPIs -->
          <div class="mb-8">
            <p class="text-xs font-semibold text-white uppercase tracking-widest mb-4">Portfolio Snapshot</p>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">

              <div class="bg-mp-card border border-mp-border rounded-xl p-5 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-20 h-20 bg-mp-teal/5 rounded-full -translate-y-4 translate-x-4"></div>
                <p class="text-xs font-semibold text-white uppercase tracking-widest mb-2">Total Value</p>
                <p class="text-2xl font-bold text-mp-text-secondary">{{ fmtM(summary.total_valuation) }}</p>
                <p class="text-xs text-mp-muted mt-1">Current portfolio</p>
              </div>

              <div class="bg-mp-card border border-mp-border rounded-xl p-5 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-20 h-20 bg-mp-success/5 rounded-full -translate-y-4 translate-x-4"></div>
                <p class="text-xs font-semibold text-mp-success uppercase tracking-widest mb-2">Invested</p>
                <p class="text-2xl font-bold text-mp-text-secondary">{{ fmtM(summary.total_invested) }}</p>
                <p class="text-xs text-mp-muted mt-1">Total capital deployed</p>
              </div>

              <div class="bg-mp-card border border-mp-border rounded-xl p-5 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-20 h-20 bg-mp-gold/5 rounded-full -translate-y-4 translate-x-4"></div>
                <p class="text-xs font-semibold text-white uppercase tracking-widest mb-2">Avg MOIC</p>
                <p class="text-2xl font-bold" :class="summary.avg_moic >= 1 ? 'text-mp-success' : 'text-mp-danger'">
                  {{ summary.avg_moic ? summary.avg_moic.toFixed(2) + 'x' : '—' }}
                </p>
                <p class="text-xs text-mp-muted mt-1">Weighted average</p>
              </div>

              <div class="bg-mp-card border border-mp-border rounded-xl p-5">
                <p class="text-xs font-semibold text-mp-warning uppercase tracking-widest mb-2">Companies</p>
                <p class="text-2xl font-bold text-mp-text-secondary">{{ summary.total_companies }}</p>
                <div class="flex gap-2 mt-2 flex-wrap">
                  <span class="text-xs px-2 py-0.5 rounded-full bg-mp-success/15 text-mp-success border border-mp-success">
                    {{ summary.investment_count }} Investments
                  </span>
                  <span class="text-xs px-2 py-0.5 rounded-full bg-mp-teal-subtle text-white border border-mp-teal">
                    {{ summary.prospect_count }} Prospects
                  </span>
                </div>
              </div>

              <div class="bg-mp-card border border-mp-border rounded-xl p-5 cursor-pointer hover:border-mp-danger/60 transition-colors"
                @click="summary.at_risk_count > 0 && (activeTab = 'redflags')">
                <p class="text-xs font-semibold text-mp-danger uppercase tracking-widest mb-2">At Risk</p>
                <p class="text-2xl font-bold" :class="summary.at_risk_count > 0 ? 'text-mp-danger' : 'text-mp-success'">
                  {{ summary.at_risk_count }}
                </p>
                <p class="text-xs mt-1" :class="summary.at_risk_count > 0 ? 'text-mp-danger' : 'text-mp-muted'">
                  {{ summary.at_risk_count === 0 ? 'All companies on track' : 'Click to see details →' }}
                </p>
              </div>

              <!-- NEW: Prospects snapshot -->
              <div class="bg-mp-card border border-mp-border rounded-xl p-5">
                <p class="text-xs font-semibold text-white uppercase tracking-widest mb-2">Pipeline</p>
                <p class="text-2xl font-bold text-mp-text-secondary">{{ fmtM(summary.prospect_total_invested) }}</p>
                <p class="text-xs text-mp-muted mt-1">{{ summary.prospect_count }} prospects tracked</p>
              </div>

            </div>
          </div>

          <!-- SECTION 2 — Portfolio Companies Grid (Investments + Prospects) -->
          <div class="mb-8">
            <div class="flex items-center justify-between mb-4">
              <p class="text-xs font-semibold text-white uppercase tracking-widest">Portfolio Companies</p>
              <div class="flex gap-2">
                <button
                  v-for="f in ['all', 'investment', 'prospect', 'exited']" :key="f"
                  @click="statusFilter = f"
                  :class="[
                    'text-xs px-3 py-1.5 rounded-lg font-medium transition-colors',
                    statusFilter === f
                      ? 'bg-mp-teal text-mp-text-secondary'
                      : 'bg-mp-card-hover text-mp-muted hover:text-mp-text-secondary hover:bg-mp-page'
                  ]"
                >
                  {{ f.charAt(0).toUpperCase() + f.slice(1) }}
                </button>
              </div>
            </div>

            <div v-if="filteredCompanies.length === 0" class="text-center py-16 text-mp-muted text-sm">
              No companies found for this filter.
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
              <div
                v-for="c in filteredCompanies" :key="c.id"
                :class="[
                  'bg-mp-card border rounded-xl overflow-hidden hover:border-mp-teal/60 transition-colors',
                  c.type === 'prospect' ? 'border-mp-teal/60' : 'border-mp-border'
                ]"
              >
                <!-- Card Header -->
                <div class="px-5 pt-5 pb-4 border-b border-mp-border">
                  <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                      <div class="w-10 h-10 rounded-lg bg-mp-card-hover border border-mp-border flex-shrink-0 flex items-center justify-center overflow-hidden">
                        <img v-if="c.logo" :src="`/storage/${c.logo}`" class="w-full h-full object-contain" />
                        <span v-else class="text-sm font-bold text-white">{{ c.name.charAt(0) }}</span>
                      </div>
                      <div class="min-w-0">
                        <div class="flex items-center gap-2">
                          <p class="text-sm font-semibold text-mp-text-secondary truncate">{{ c.name }}</p>
                          <!-- Type badge -->
                          <span v-if="c.type === 'prospect'" class="flex-shrink-0 text-xs px-1.5 py-0.5 rounded-full bg-mp-teal-subtle/40 text-white border border-mp-teal font-medium">Prospect</span>
                          <span v-else class="flex-shrink-0 text-xs px-1.5 py-0.5 rounded-full bg-mp-gold/15 text-white border border-mp-gold font-medium">Investment</span>
                        </div>
                        <p class="text-xs text-mp-muted truncate">{{ c.sector || 'No sector' }}</p>
                      </div>
                    </div>
                    <span :class="statusBadge(c.status)" class="text-xs px-2 py-1 rounded-full font-semibold flex-shrink-0">
                      {{ c.status }}
                    </span>
                  </div>
                </div>

                <!-- Card Metrics — Current Valuation prominently shown -->
                <div class="px-5 py-4 border-b border-mp-border">
                  <!-- Valuation highlight row -->
                  <div v-if="c.current_valuation" class="mb-3 bg-mp-card-hover/60 rounded-lg px-3 py-2 flex items-center justify-between">
                    <span class="text-xs text-mp-muted">Current Valuation</span>
                    <span class="text-base font-bold text-mp-success">{{ fmtM(c.current_valuation) }} <span class="text-xs font-normal text-mp-muted">{{ c.invested_currency }}</span></span>
                  </div>
                  <div class="grid grid-cols-3 gap-3">
                    <div>
                      <p class="text-xs text-mp-muted mb-1">Invested</p>
                      <p class="text-sm font-semibold text-mp-text-secondary">{{ fmtM(c.invested_amount) }}</p>
                    </div>
                    <div>
                      <p class="text-xs text-mp-muted mb-1">Entry Val.</p>
                      <p class="text-sm font-semibold text-mp-text">{{ c.entry_valuation ? fmtM(c.entry_valuation) : '—' }}</p>
                    </div>
                    <div>
                      <p class="text-xs text-mp-muted mb-1">MOIC</p>
                      <p class="text-sm font-bold" :class="c.moic >= 1 ? 'text-mp-success' : c.moic ? 'text-mp-danger' : 'text-mp-muted'">
                        {{ c.moic ? c.moic.toFixed(2) + 'x' : '—' }}
                      </p>
                    </div>
                    <div>
                      <p class="text-xs text-mp-muted mb-1">IRR</p>
                      <p class="text-sm font-bold" :class="c.irr >= 15 ? 'text-mp-success' : c.irr ? 'text-mp-warning' : 'text-mp-muted'">
                        {{ c.irr ? c.irr.toFixed(1) + '%' : '—' }}
                      </p>
                    </div>
                    <div>
                      <p class="text-xs text-mp-muted mb-1">Equity</p>
                      <p class="text-sm font-semibold text-mp-text-secondary">{{ c.equity_stake ? c.equity_stake.toFixed(1)*100 + '%' : '—' }}</p>
                    </div>
                    <div>
                      <p class="text-xs text-mp-muted mb-1">Currency</p>
                      <p class="text-sm font-semibold text-mp-text-secondary">{{ c.invested_currency || '—' }}</p>
                    </div>
                  </div>
                </div>

                <!-- Latest Financials strip -->
                <div v-if="c.last_revenue !== null || c.last_net_profit !== null" class="px-5 py-3 border-b border-mp-border bg-mp-card-hover/20">
                  <p class="text-xs text-mp-muted mb-2">Latest Financials <span class="text-mp-muted">({{ c.last_fs_period || '—' }})</span></p>
                  <div class="flex gap-4">
                    <div v-if="c.last_revenue !== null">
                      <p class="text-xs text-mp-muted">Revenue</p>
                      <p class="text-sm font-semibold text-white">{{ fmtM(c.last_revenue) }}</p>
                    </div>
                    <div v-if="c.last_net_profit !== null">
                      <p class="text-xs text-mp-muted">Net Profit</p>
                      <p class="text-sm font-semibold" :class="c.last_net_profit >= 0 ? 'text-mp-success' : 'text-mp-danger'">
                        {{ fmtM(c.last_net_profit) }}
                      </p>
                    </div>
                  </div>
                </div>

                <!-- KPI status strip -->
                <div class="px-5 py-3 border-b border-mp-border flex items-center gap-2">
                  <span class="text-xs text-mp-muted">KPIs:</span>
                  <template v-if="c.kpi_on_track > 0 || c.kpi_at_risk > 0">
                    <span v-if="c.kpi_on_track > 0" class="text-xs px-2 py-0.5 rounded-full bg-mp-success/15 text-mp-success border border-mp-success">
                      {{ c.kpi_on_track }} on track
                    </span>
                    <span v-if="c.kpi_at_risk > 0" class="text-xs px-2 py-0.5 rounded-full bg-mp-danger/15 text-mp-danger border border-mp-danger">
                      {{ c.kpi_at_risk }} at risk
                    </span>
                  </template>
                  <span v-else class="text-xs text-mp-muted">No KPI data</span>

                  <!-- Notes indicator -->
                  <button
                    v-if="(c.notes_count || 0) > 0"
                    @click="openNotesPanel(c)"
                    class="ml-auto text-xs px-2 py-0.5 rounded-full bg-mp-gold/15 text-mp-warning border border-mp-gold hover:bg-mp-gold/15 transition-colors"
                  >
                    📝 {{ c.notes_count }} note{{ c.notes_count > 1 ? 's' : '' }}
                  </button>
                </div>

                <!-- Actions -->
                <div class="px-5 py-3 flex gap-2">
                  <Link
                    :href="`/portfolio-companies/${c.id}`"
                    class="flex-1 text-center text-xs font-medium py-1.5 rounded-lg bg-mp-teal hover:bg-mp-teal-dark text-mp-text-secondary transition-colors"
                  >
                    Dashboard
                  </Link>
                  <Link
                    :href="`/portfolio-companies/${c.id}/financial-statements`"
                    class="flex-1 text-center text-xs font-medium py-1.5 rounded-lg bg-mp-card-hover hover:bg-mp-page text-mp-text transition-colors"
                  >
                    Financials
                  </Link>
                  <Link
                    :href="`/portfolio-companies/${c.id}/kpi`"
                    class="flex-1 text-center text-xs font-medium py-1.5 rounded-lg bg-mp-card-hover hover:bg-mp-page text-mp-text transition-colors"
                  >
                    KPIs
                  </Link>
                  <button
                    @click="openNotesPanel(c)"
                    class="flex-shrink-0 text-center text-xs font-medium py-1.5 px-2.5 rounded-lg transition-colors"
                    :class="(c.notes_count || 0) > 0 ? 'bg-mp-gold/40 hover:bg-mp-gold/60 text-mp-warning' : 'bg-mp-card-hover hover:bg-mp-page text-mp-muted'"
                    title="Company Notes"
                  >
                    📝
                  </button>
                </div>

              </div>
            </div>
          </div>

          <!-- SECTION 3 — Sector + Status -->
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <div class="bg-mp-card border border-mp-border rounded-xl overflow-hidden">
              <div class="px-6 py-4 border-b border-mp-border">
                <p class="text-xs font-semibold text-white uppercase tracking-widest">Sector Breakdown</p>
              </div>
              <div class="p-5 space-y-3">
                <div v-for="s in sectorBreakdown" :key="s.sector" class="flex items-center gap-3">
                  <div class="w-28 text-xs text-mp-muted truncate flex-shrink-0">{{ s.sector }}</div>
                  <div class="flex-1 h-2 bg-mp-card-hover rounded-full overflow-hidden">
                    <div class="h-2 bg-mp-teal rounded-full" :style="{ width: s.pct + '%' }"></div>
                  </div>
                  <div class="w-16 text-right">
                    <span class="text-xs text-mp-text">{{ s.count }}</span>
                    <span class="text-xs text-mp-muted ml-1">{{ s.pct }}%</span>
                  </div>
                </div>
                <div v-if="sectorBreakdown.length === 0" class="text-mp-muted text-sm text-center py-4">No sector data</div>
              </div>
            </div>

            <div class="bg-mp-card border border-mp-border rounded-xl overflow-hidden">
              <div class="px-6 py-4 border-b border-mp-border">
                <p class="text-xs font-semibold text-white uppercase tracking-widest">Portfolio Mix</p>
              </div>
              <div class="p-5">
                <div class="space-y-4">
                  <div v-for="s in statusMix" :key="s.label">
                    <div class="flex items-center justify-between mb-1">
                      <span class="text-xs text-mp-muted">{{ s.label }}</span>
                      <span class="text-xs font-semibold text-mp-text-secondary">{{ s.count }} <span class="text-mp-muted font-normal">({{ s.pct }}%)</span></span>
                    </div>
                    <div class="h-2.5 bg-mp-card-hover rounded-full overflow-hidden">
                      <div :class="s.color" class="h-2.5 rounded-full" :style="{ width: s.pct + '%' }"></div>
                    </div>
                  </div>
                </div>
                <div class="mt-6 grid grid-cols-2 gap-3">
                  <div class="bg-mp-card-hover rounded-lg p-3">
                    <p class="text-xs text-mp-muted mb-1">Total Invested</p>
                    <p class="text-base font-bold text-mp-text-secondary">{{ fmtM(summary.total_invested) }}</p>
                  </div>
                  <div class="bg-mp-card-hover rounded-lg p-3">
                    <p class="text-xs text-mp-muted mb-1">Portfolio Value</p>
                    <p class="text-base font-bold" :class="summary.total_valuation >= summary.total_invested ? 'text-mp-success' : 'text-mp-danger'">
                      {{ fmtM(summary.total_valuation) }}
                    </p>
                  </div>
                </div>
              </div>
            </div>

          </div>

        </div>

        <!-- ══════════════════════════════════════════
             TAB: PERFORMANCE
        ═══════════════════════════════════════════ -->
        <div v-if="activeTab === 'performance'">

          <div class="mb-6">
            <p class="text-xs font-semibold text-white uppercase tracking-widest mb-1">Portfolio Performance</p>
            <p class="text-sm text-mp-muted">Compare investment metrics across all companies.</p>
          </div>

          <!-- Ranking chart -->
          <div class="bg-mp-card border border-mp-border rounded-xl overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-mp-border flex items-center justify-between">
              <p class="text-xs font-semibold text-white uppercase tracking-widest">Investment Ranking</p>
              <div class="flex gap-2">
                <button
                  v-for="m in ['moic', 'irr', 'valuation']" :key="m"
                  @click="perfMetric = m"
                  :class="perfMetric === m ? 'bg-mp-teal text-mp-text-secondary' : 'bg-mp-card-hover text-mp-muted hover:text-mp-text-secondary'"
                  class="text-xs px-3 py-1 rounded-lg font-medium transition-colors"
                >
                  {{ m.toUpperCase() }}
                </button>
              </div>
            </div>
            <div class="p-5 space-y-3">
              <div v-for="(c, i) in rankedCompanies" :key="c.id" class="flex items-center gap-4">
                <span class="text-xs text-mp-muted w-5 text-right flex-shrink-0">#{{ i + 1 }}</span>
                <div class="w-36 flex items-center gap-1.5 flex-shrink-0">
                  <span class="text-xs text-mp-text truncate">{{ c.name }}</span>
                  <span v-if="c.type === 'prospect'" class="text-xs px-1 rounded-full bg-mp-teal-subtle/40 text-white border border-mp-teal flex-shrink-0">P</span>
                </div>
                <div class="flex-1 h-3 bg-mp-card-hover rounded-full overflow-hidden">
                  <div :class="perfBarColor(c)" class="h-3 rounded-full transition-all" :style="{ width: perfBarWidth(c, i) + '%' }"></div>
                </div>
                <div class="w-20 text-right flex-shrink-0">
                  <span class="text-sm font-bold" :class="perfValueColor(c)">{{ perfValue(c) }}</span>
                </div>
              </div>
              <div v-if="rankedCompanies.length === 0" class="text-mp-muted text-sm text-center py-6">
                No performance data available yet.
              </div>
            </div>
          </div>

          <!-- Budget Variance P&L Section -->
          <div class="bg-mp-card border border-mp-border rounded-xl overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-mp-border flex items-center justify-between">
              <div>
                <p class="text-xs font-semibold text-white uppercase tracking-widest">Budget vs Actual — YTD P&L</p>
                <p class="text-xs text-mp-muted mt-0.5">Across all companies with active budgets</p>
              </div>
              <span class="text-xs text-mp-muted">{{ currentYear }}</span>
            </div>

            <div v-if="companiesWithBudget.length === 0" class="px-6 py-10 text-center">
              <p class="text-mp-muted text-sm">No budget data available yet.</p>
              <p class="text-mp-muted text-xs mt-1">Create budgets for your portfolio companies to see variance here.</p>
            </div>

            <div v-else class="overflow-x-auto">
              <table class="w-full text-sm">
                <thead>
                  <tr class="border-b border-mp-border">
                    <th class="text-left text-xs font-semibold text-white uppercase tracking-widest px-6 py-3 sticky left-0 bg-mp-card">Company</th>
                    <th class="text-right text-xs font-semibold text-white uppercase tracking-widest px-4 py-3">Revenue Bgt</th>
                    <th class="text-right text-xs font-semibold text-white uppercase tracking-widest px-4 py-3">Revenue Act</th>
                    <th class="text-right text-xs font-semibold text-white uppercase tracking-widest px-4 py-3">Rev Var%</th>
                    <th class="text-right text-xs font-semibold text-white uppercase tracking-widest px-4 py-3">EBITDA Bgt</th>
                    <th class="text-right text-xs font-semibold text-white uppercase tracking-widest px-4 py-3">EBITDA Act</th>
                    <th class="text-right text-xs font-semibold text-white uppercase tracking-widest px-4 py-3">EBITDA Var%</th>
                    <th class="text-right text-xs font-semibold text-white uppercase tracking-widest px-4 py-3">Net Profit Bgt</th>
                    <th class="text-right text-xs font-semibold text-white uppercase tracking-widest px-4 py-3">Net Profit Act</th>
                    <th class="text-right text-xs font-semibold text-white uppercase tracking-widest px-4 py-3">Net Var%</th>
                    <th class="px-4 py-3"></th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                  <tr v-for="c in companiesWithBudget" :key="c.id" class="hover:bg-mp-card-hover/30 transition-colors">
                    <td class="px-6 py-3 sticky left-0 bg-mp-card">
                      <div class="flex items-center gap-2">
                        <span class="font-semibold text-mp-text-secondary">{{ c.name }}</span>
                        <span v-if="c.type === 'prospect'" class="text-xs px-1.5 py-0.5 rounded-full bg-mp-teal-subtle/40 text-white border border-mp-teal">P</span>
                      </div>
                      <p class="text-xs text-mp-muted mt-0.5">{{ c.budget_name }} · {{ c.budget_ytd_months }}M YTD</p>
                    </td>

                    <!-- Revenue -->
                    <template v-if="c.budget_variance">
                      <td class="px-4 py-3 text-right text-mp-muted text-xs">{{ fmtM(getBvRow(c, 'sales_revenue', 'budget')) }}</td>
                      <td class="px-4 py-3 text-right text-mp-text-secondary font-medium text-xs">{{ fmtM(getBvRow(c, 'sales_revenue', 'actual')) }}</td>
                      <td class="px-4 py-3 text-right">
                        <span :class="varPillClass(getBvRow(c, 'sales_revenue', 'var_pct'), false)" class="text-xs px-2 py-0.5 rounded-full font-semibold border">
                          {{ fmtPct(getBvRow(c, 'sales_revenue', 'var_pct')) }}
                        </span>
                      </td>
                      <!-- EBITDA -->
                      <td class="px-4 py-3 text-right text-mp-muted text-xs">{{ fmtM(getBvRow(c, 'ebitda', 'budget')) }}</td>
                      <td class="px-4 py-3 text-right text-mp-text-secondary font-medium text-xs">{{ fmtM(getBvRow(c, 'ebitda', 'actual')) }}</td>
                      <td class="px-4 py-3 text-right">
                        <span :class="varPillClass(getBvRow(c, 'ebitda', 'var_pct'), false)" class="text-xs px-2 py-0.5 rounded-full font-semibold border">
                          {{ fmtPct(getBvRow(c, 'ebitda', 'var_pct')) }}
                        </span>
                      </td>
                      <!-- Net Profit -->
                      <td class="px-4 py-3 text-right text-mp-muted text-xs">{{ fmtM(getBvRow(c, 'net_profit', 'budget')) }}</td>
                      <td class="px-4 py-3 text-right text-mp-text-secondary font-medium text-xs">{{ fmtM(getBvRow(c, 'net_profit', 'actual')) }}</td>
                      <td class="px-4 py-3 text-right">
                        <span :class="varPillClass(getBvRow(c, 'net_profit', 'var_pct'), false)" class="text-xs px-2 py-0.5 rounded-full font-semibold border">
                          {{ fmtPct(getBvRow(c, 'net_profit', 'var_pct')) }}
                        </span>
                      </td>
                    </template>
                    <template v-else>
                      <td colspan="9" class="px-4 py-3 text-center text-xs text-mp-muted">No actuals entered yet</td>
                    </template>

                    <td class="px-4 py-3">
                      <Link :href="`/portfolio-companies/${c.id}/budgets/${c.budget_id}`" class="text-xs text-white hover:text-white">Variance →</Link>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Full table -->
          <div class="bg-mp-card border border-mp-border rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-mp-border flex items-center justify-between">
              <p class="text-xs font-semibold text-white uppercase tracking-widest">Full Portfolio Table</p>
              <div class="flex gap-2">
                <button
                  v-for="f in ['all', 'investment', 'prospect']" :key="f"
                  @click="tableFilter = f"
                  :class="tableFilter === f ? 'bg-mp-teal text-mp-text-secondary' : 'bg-mp-card-hover text-mp-muted hover:text-mp-text-secondary'"
                  class="text-xs px-3 py-1 rounded-lg font-medium transition-colors"
                >
                  {{ f.charAt(0).toUpperCase() + f.slice(1) }}
                </button>
              </div>
            </div>
            <div class="overflow-x-auto">
              <table class="w-full text-sm">
                <thead>
                  <tr class="border-b border-mp-border">
                    <th class="text-left text-xs font-semibold text-white uppercase tracking-widest px-6 py-3">Company</th>
                    <th class="text-left text-xs font-semibold text-white uppercase tracking-widest px-4 py-3">Type</th>
                    <th class="text-left text-xs font-semibold text-white uppercase tracking-widest px-4 py-3">Sector</th>
                    <th class="text-right text-xs font-semibold text-white uppercase tracking-widest px-4 py-3">Invested</th>
                    <th class="text-right text-xs font-semibold text-white uppercase tracking-widest px-4 py-3">Entry Val.</th>
                    <th class="text-right text-xs font-semibold text-white uppercase tracking-widest px-4 py-3">Current Val.</th>
                    <th class="text-right text-xs font-semibold text-white uppercase tracking-widest px-4 py-3">MOIC</th>
                    <th class="text-right text-xs font-semibold text-white uppercase tracking-widest px-4 py-3">IRR</th>
                    <th class="text-right text-xs font-semibold text-white uppercase tracking-widest px-4 py-3">Equity %</th>
                    <th class="text-center text-xs font-semibold text-white uppercase tracking-widest px-4 py-3">Status</th>
                    <th class="px-4 py-3"></th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                  <template v-for="c in filteredTableCompanies" :key="c.id">
                    <tr class="hover:bg-mp-card-hover/40 transition-colors">
                      <td class="px-6 py-3 font-semibold text-mp-text-secondary">{{ c.name }}</td>
                      <td class="px-4 py-3">
                        <span v-if="c.type === 'prospect'" class="text-xs px-2 py-0.5 rounded-full bg-mp-teal-subtle/40 text-white border border-mp-teal">Prospect</span>
                        <span v-else class="text-xs px-2 py-0.5 rounded-full bg-mp-gold/15 text-white border border-mp-gold">Investment</span>
                      </td>
                      <td class="px-4 py-3 text-mp-muted text-xs">{{ c.sector || '—' }}</td>
                      <td class="px-4 py-3 text-right text-mp-text">{{ fmtM(c.invested_amount) }}</td>
                      <td class="px-4 py-3 text-right text-mp-muted">{{ c.entry_valuation ? fmtM(c.entry_valuation) : '—' }}</td>
                      <td class="px-4 py-3 text-right">
                        <span v-if="c.current_valuation" class="font-semibold text-mp-success">{{ fmtM(c.current_valuation) }}</span>
                        <span v-else class="text-mp-muted">—</span>
                      </td>
                      <td class="px-4 py-3 text-right">
                        <span :class="c.moic >= 1 ? 'text-mp-success' : c.moic ? 'text-mp-danger' : 'text-mp-muted'" class="font-bold">
                          {{ c.moic ? c.moic.toFixed(2) + 'x' : '—' }}
                        </span>
                      </td>
                      <td class="px-4 py-3 text-right">
                        <span :class="c.irr >= 15 ? 'text-mp-success' : c.irr ? 'text-mp-warning' : 'text-mp-muted'" class="font-bold">
                          {{ c.irr ? c.irr.toFixed(1) + '%' : '—' }}
                        </span>
                      </td>
                      <td class="px-4 py-3 text-right text-mp-text">{{ c.equity_stake ? c.equity_stake.toFixed(1)*100 + '%' : '—' }}</td>
                      <td class="px-4 py-3 text-center">
                        <span :class="statusBadge(c.status)" class="text-xs px-2 py-0.5 rounded-full font-semibold">{{ c.status }}</span>
                      </td>
                      <td class="px-4 py-3">
                        <Link :href="`/portfolio-companies/${c.id}`" class="text-xs text-white hover:text-white">View →</Link>
                      </td>
                    </tr>
                  </template>
                </tbody>
              </table>
            </div>
          </div>

        </div>

        <!-- ══════════════════════════════════════════
             TAB: RED FLAGS
        ═══════════════════════════════════════════ -->
        <div v-if="activeTab === 'redflags'">

          <div class="mb-6">
            <p class="text-xs font-semibold text-mp-danger uppercase tracking-widest mb-1">Attention Required</p>
            <p class="text-sm text-mp-muted">
              KPIs are flagged when actual is more than 10% below target (for higher-is-better metrics) or more than 10% above target (for lower-is-better metrics).
            </p>
          </div>

          <!-- At-risk KPI table -->
          <div class="bg-mp-card border border-mp-border rounded-xl overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-mp-border flex items-center justify-between">
              <p class="text-xs font-semibold text-mp-danger uppercase tracking-widest">At-Risk & Watch KPIs</p>
              <div class="flex items-center gap-3">
                <span v-if="atRiskKpis.length > 0" class="text-xs px-2 py-1 rounded-full bg-mp-danger/15 text-mp-danger border border-mp-danger font-semibold">
                  {{ atRiskKpis.filter(k => k.status === 'at_risk').length }} at risk
                </span>
                <span v-if="atRiskKpis.filter(k => k.status === 'watch').length > 0" class="text-xs px-2 py-1 rounded-full bg-mp-warning/15 text-mp-warning border border-mp-warning font-semibold">
                  {{ atRiskKpis.filter(k => k.status === 'watch').length }} watch
                </span>
                <span v-if="atRiskKpis.length === 0" class="text-xs text-mp-muted">0 flagged</span>
              </div>
            </div>

            <div v-if="atRiskKpis.length === 0" class="px-6 py-12 text-center">
              <div class="text-4xl mb-3">🎉</div>
              <p class="text-mp-text-secondary font-semibold mb-1">All KPIs on track</p>
              <p class="text-mp-muted text-sm max-w-md mx-auto">
                No KPIs are currently flagged. This section will show KPIs once actual values fall below 90% of their targets.
              </p>
            </div>

            <div v-else class="overflow-x-auto">
              <table class="w-full text-sm">
                <thead>
                  <tr class="border-b border-mp-border">
                    <th class="text-left text-xs font-semibold text-white uppercase tracking-widest px-6 py-3">Company</th>
                    <th class="text-left text-xs font-semibold text-white uppercase tracking-widest px-4 py-3">KPI</th>
                    <th class="text-right text-xs font-semibold text-white uppercase tracking-widest px-4 py-3">Target</th>
                    <th class="text-right text-xs font-semibold text-white uppercase tracking-widest px-4 py-3">Actual</th>
                    <th class="text-right text-xs font-semibold text-white uppercase tracking-widest px-4 py-3">Variance</th>
                    <th class="text-center text-xs font-semibold text-white uppercase tracking-widest px-4 py-3">Status</th>
                    <th class="px-4 py-3"></th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                  <tr v-for="k in atRiskKpis" :key="k.company_id + '-' + k.kpi_name"
                    :class="k.status === 'at_risk' ? 'hover:bg-mp-danger/10' : 'hover:bg-mp-warning/10'">
                    <td class="px-6 py-3 text-mp-text-secondary font-medium">{{ k.company_name }}</td>
                    <td class="px-4 py-3 text-mp-text">{{ k.kpi_name }}</td>
                    <td class="px-4 py-3 text-right text-mp-muted">{{ fmt(k.target) }}</td>
                    <td class="px-4 py-3 text-right text-mp-text-secondary font-semibold">{{ fmt(k.actual) }}</td>
                    <td class="px-4 py-3 text-right">
                      <span :class="k.status === 'at_risk' ? 'bg-mp-danger/15 text-mp-danger border-mp-danger' : 'bg-mp-warning/15 text-mp-warning border-mp-warning'"
                        class="text-xs px-2 py-0.5 rounded-full font-semibold border">
                        {{ k.variance_pct !== null ? (k.variance_pct > 0 ? '+' : '') + k.variance_pct.toFixed(1) + '%' : '—' }}
                      </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                      <span :class="k.status === 'at_risk' ? 'bg-mp-danger/15 text-mp-danger border-mp-danger' : 'bg-mp-warning/15 text-mp-warning border-mp-warning'"
                        class="text-xs px-2 py-0.5 rounded-full font-semibold border">
                        {{ k.status === 'at_risk' ? '🔴 At Risk' : '🟡 Watch' }}
                      </span>
                    </td>
                    <td class="px-4 py-3">
                      <Link :href="`/portfolio-companies/${k.company_id}/kpi`" class="text-xs text-white hover:text-white">View →</Link>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- MOIC below 1x -->
          <div class="bg-mp-card border border-mp-border rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-mp-border">
              <p class="text-xs font-semibold text-mp-warning uppercase tracking-widest">MOIC Below 1.0x</p>
              <p class="text-xs text-mp-muted mt-1">Companies where current valuation is below invested capital</p>
            </div>
            <div v-if="lowMoicCompanies.length === 0" class="px-6 py-10 text-center">
              <p class="text-mp-muted text-sm">✅ All valued companies are above invested cost.</p>
            </div>
            <div v-else class="divide-y divide-gray-800">
              <div v-for="c in lowMoicCompanies" :key="c.id" class="px-6 py-4 flex items-center justify-between hover:bg-mp-card-hover/30">
                <div>
                  <p class="text-sm font-semibold text-mp-text-secondary">{{ c.name }}</p>
                  <p class="text-xs text-mp-muted">{{ c.sector || 'No sector' }} · Invested {{ fmtM(c.invested_amount) }}</p>
                </div>
                <div class="flex items-center gap-4">
                  <div class="text-right">
                    <p class="text-xs text-mp-muted">MOIC</p>
                    <p class="text-base font-bold text-mp-danger">{{ c.moic ? c.moic.toFixed(2) + 'x' : '—' }}</p>
                  </div>
                  <Link :href="`/portfolio-companies/${c.id}`" class="text-xs text-white hover:text-white">View →</Link>
                </div>
              </div>
            </div>
          </div>

        </div>

        <!-- ══════════════════════════════════════════
             TAB: ACTIVITY
        ═══════════════════════════════════════════ -->
        <div v-if="activeTab === 'activity'">

          <div class="mb-6">
            <p class="text-xs font-semibold text-white uppercase tracking-widest mb-1">Recent Activity</p>
            <p class="text-sm text-mp-muted">Latest actions across your portfolio.</p>
          </div>

          <div class="bg-mp-card border border-mp-border rounded-xl overflow-hidden">
            <div v-if="recentActivity.length === 0" class="px-6 py-10 text-center text-mp-muted text-sm">
              No recent activity found.
            </div>
            <div v-else class="divide-y divide-gray-800">
              <div
                v-for="(item, i) in recentActivity" :key="i"
                class="px-6 py-4 flex items-start gap-4 hover:bg-mp-card-hover/30 transition-colors"
              >
                <div :class="activityIconBg(item.type)" class="w-9 h-9 rounded-lg flex-shrink-0 flex items-center justify-center text-base mt-0.5">
                  {{ activityIcon(item.type) }}
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm text-mp-text-secondary">
                    <span class="font-semibold">{{ item.company_name }}</span>
                    <span class="text-mp-muted font-normal"> — {{ item.description }}</span>
                  </p>
                  <p class="text-xs text-mp-muted mt-0.5">{{ item.created_at }}</p>
                </div>
                <Link v-if="item.link" :href="item.link" class="text-xs text-white hover:text-white flex-shrink-0 mt-1">
                  View →
                </Link>
              </div>
            </div>
          </div>

        </div>

      </div>
    </div>

    <!-- ══════════════════════════════════════════
         NOTES SLIDE-OUT PANEL
    ═══════════════════════════════════════════ -->
    <transition name="slide-panel">
      <div v-if="notesPanel.open" class="fixed inset-0 z-50 flex justify-end">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/50" @click="closeNotesPanel"></div>

        <!-- Panel -->
        <div class="relative z-10 w-full max-w-md bg-mp-card border-l border-mp-border h-full flex flex-col shadow-2xl">

          <!-- Panel Header -->
          <div class="px-6 py-5 border-b border-mp-border flex items-center justify-between">
            <div>
              <p class="text-xs font-semibold text-mp-warning uppercase tracking-widest">Company Notes</p>
              <p class="text-base font-bold text-mp-text-secondary mt-0.5">{{ notesPanel.company?.name }}</p>
            </div>
            <button @click="closeNotesPanel" class="text-mp-muted hover:text-mp-text-secondary transition-colors">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Add Note Form -->
          <div class="px-6 py-4 border-b border-mp-border">
            <p class="text-xs font-semibold text-white uppercase tracking-widest mb-3">Add Note</p>
            <div class="space-y-3">
              <textarea
                v-model="noteForm.note"
                rows="3"
                placeholder="Observation, concern, or update about this company..."
                class="w-full bg-mp-card-hover border border-mp-border text-mp-text-secondary text-sm rounded-lg px-3 py-2 resize-none focus:outline-none focus:border-mp-teal placeholder-gray-600"
              ></textarea>
              <input
                v-model="noteForm.action_items"
                type="text"
                placeholder="Action item (optional)"
                class="w-full bg-mp-card-hover border border-mp-border text-mp-text-secondary text-sm rounded-lg px-3 py-2 focus:outline-none focus:border-mp-teal placeholder-gray-600"
              />
              <div class="flex items-center gap-3">
                <label class="text-xs text-mp-muted">Priority:</label>
                <div class="flex gap-2">
                  <button
                    v-for="p in ['high', 'medium', 'low']" :key="p"
                    @click="noteForm.priority = p"
                    :class="[
                      'text-xs px-2.5 py-1 rounded-lg font-medium transition-colors border',
                      noteForm.priority === p ? priorityActive(p) : 'bg-mp-card-hover text-mp-muted border-mp-border'
                    ]"
                  >
                    {{ p.charAt(0).toUpperCase() + p.slice(1) }}
                  </button>
                </div>
                <button
                  @click="saveNote"
                  :disabled="!noteForm.note.trim() || noteForm.saving"
                  class="ml-auto text-xs px-4 py-1.5 bg-mp-teal hover:bg-mp-teal-dark disabled:opacity-50 disabled:cursor-not-allowed text-mp-text-secondary rounded-lg font-medium transition-colors"
                >
                  {{ noteForm.saving ? 'Saving...' : 'Save' }}
                </button>
              </div>
            </div>
          </div>

          <!-- Notes List -->
          <div class="flex-1 overflow-y-auto px-6 py-4 space-y-3">
            <div v-if="notesPanel.loading" class="text-center py-8 text-mp-muted text-sm">Loading notes...</div>
            <div v-else-if="notesPanel.notes.length === 0" class="text-center py-8">
              <p class="text-mp-muted text-sm">No notes yet.</p>
              <p class="text-mp-muted text-xs mt-1">Add your first note about this company above.</p>
            </div>
            <div
              v-else
              v-for="n in notesPanel.notes" :key="n.id"
              class="bg-mp-card-hover rounded-xl p-4 border border-mp-border"
            >
              <div class="flex items-start justify-between gap-2 mb-2">
                <div class="flex items-center gap-2 flex-wrap">
                  <span :class="priorityBadge(n.priority)" class="text-xs px-2 py-0.5 rounded-full font-semibold border">
                    {{ n.priority }}
                  </span>
                  <span class="text-xs text-mp-muted">{{ n.author }} · {{ n.created_at }}</span>
                </div>
                <button @click="deleteNote(n)" class="text-mp-muted hover:text-mp-danger transition-colors flex-shrink-0">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
              <p class="text-sm text-mp-text-secondary leading-relaxed">{{ n.note }}</p>
              <div v-if="n.action_items" class="mt-2 flex items-start gap-1.5">
                <span class="text-mp-warning text-xs mt-0.5">⚡</span>
                <p class="text-xs text-mp-warning">{{ n.action_items }}</p>
              </div>
            </div>
          </div>

        </div>
      </div>
    </transition>

  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
  companies:      { type: Array,  default: () => [] },
  atRiskKpis:     { type: Array,  default: () => [] },
  recentActivity: { type: Array,  default: () => [] },
  org:            { type: Object, default: () => ({ name: 'Organization' }) },
  allOrgs:        { type: Array,  default: () => [] },
  currentOrgId:   { type: Number, default: null },
})

const page = usePage()
const safeOrgId = computed(() =>
  props.currentOrgId
  ?? props.org?.id
  ?? page.props.auth?.user?.organization_id
  ?? null
)

const activeTab    = ref('overview')
const statusFilter = ref('all')
const tableFilter  = ref('all')
const perfMetric   = ref('moic')
const currentYear  = new Date().getFullYear()

const today = new Date().toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })

const tabs = [
  { key: 'overview',    label: '📊 Overview' },
  { key: 'performance', label: '🏆 Performance' },
  { key: 'redflags',    label: '🚨 Red Flags' },
  { key: 'activity',    label: '🕐 Activity' },
]

// ── Summary ───────────────────────────────────────────────────────────────────
const summary = computed(() => {
  const all        = props.companies
  const withMoic   = all.filter(c => c.moic !== null && c.moic !== undefined)
  const avgMoic    = withMoic.length ? withMoic.reduce((s, c) => s + c.moic, 0) / withMoic.length : null
  const atRiskIds  = props.atRiskKpis.length > 0
    ? new Set(props.atRiskKpis.map(k => k.company_id)).size : 0
  const investments = all.filter(c => c.type === 'investment')
  const prospects   = all.filter(c => c.type === 'prospect')
  return {
    total_companies:         all.length,
    investment_count:        investments.length,
    prospect_count:          prospects.length,
    total_invested:          investments.reduce((s, c) => s + (c.invested_amount || 0), 0),
    prospect_total_invested: prospects.reduce((s, c) => s + (c.invested_amount || 0), 0),
    total_valuation:         investments.reduce((s, c) => s + (c.current_valuation || 0), 0),
    avg_moic:                avgMoic,
    at_risk_count:           atRiskIds,
  }
})

// Filter for cards: 'all' shows both investments AND prospects
const filteredCompanies = computed(() => {
  if (statusFilter.value === 'all') return props.companies
  return props.companies.filter(c => c.type === statusFilter.value)
})

// Filter for full table
const filteredTableCompanies = computed(() => {
  if (tableFilter.value === 'all') return props.companies
  return props.companies.filter(c => c.type === tableFilter.value)
})

const sectorBreakdown = computed(() => {
  const map = {}
  props.companies.forEach(c => { const s = c.sector || 'Unclassified'; map[s] = (map[s] || 0) + 1 })
  const total = props.companies.length || 1
  return Object.entries(map)
    .map(([sector, count]) => ({ sector, count, pct: Math.round(count / total * 100) }))
    .sort((a, b) => b.count - a.count)
})

const statusMix = computed(() => {
  const total = props.companies.length || 1
  return [
    { label: 'Investments', count: summary.value.investment_count, pct: Math.round(summary.value.investment_count / total * 100), color: 'bg-mp-gold' },
    { label: 'Prospects',   count: summary.value.prospect_count,   pct: Math.round(summary.value.prospect_count / total * 100),   color: 'bg-mp-teal' },
  ]
})

const lowMoicCompanies = computed(() =>
  props.companies.filter(c => c.moic !== null && c.moic < 1.0)
)

// ── Performance tab ───────────────────────────────────────────────────────────
const rankedCompanies = computed(() => {
  const m = perfMetric.value
  return [...props.companies]
    .filter(c => m === 'moic' ? c.moic != null : m === 'irr' ? c.irr != null : c.current_valuation != null)
    .sort((a, b) => {
      if (m === 'moic')      return (b.moic || 0) - (a.moic || 0)
      if (m === 'irr')       return (b.irr || 0) - (a.irr || 0)
      return (b.current_valuation || 0) - (a.current_valuation || 0)
    })
})

// Companies that have budget data
const companiesWithBudget = computed(() =>
  props.companies.filter(c => c.budget_id)
)

function getBvRow(c, key, field) {
  if (!c.budget_variance) return null
  const row = c.budget_variance.find(r => r.key === key)
  return row ? row[field] : null
}

function perfValue(c) {
  if (perfMetric.value === 'moic')      return c.moic ? c.moic.toFixed(2) + 'x' : '—'
  if (perfMetric.value === 'irr')       return c.irr ? c.irr.toFixed(1) + '%' : '—'
  return c.current_valuation ? fmtM(c.current_valuation) : '—'
}
function perfValueColor(c) {
  if (perfMetric.value === 'moic') return c.moic >= 1 ? 'text-mp-success' : 'text-mp-danger'
  if (perfMetric.value === 'irr')  return c.irr >= 15 ? 'text-mp-success' : 'text-mp-warning'
  return 'text-mp-text-secondary'
}
function perfBarColor(c) {
  if (perfMetric.value === 'moic') return c.moic >= 1 ? 'bg-mp-success' : 'bg-mp-danger'
  if (perfMetric.value === 'irr')  return c.irr >= 15 ? 'bg-mp-success' : 'bg-mp-warning'
  return 'bg-mp-teal'
}
function perfBarWidth(c, idx) {
  if (rankedCompanies.value.length === 0 || idx === 0) return 100
  const top  = rankedCompanies.value[0]
  const m    = perfMetric.value
  const topV = m === 'moic' ? top.moic : m === 'irr' ? top.irr : top.current_valuation
  const curV = m === 'moic' ? c.moic   : m === 'irr' ? c.irr   : c.current_valuation
  return Math.max(4, Math.round((curV || 0) / (topV || 1) * 100))
}

// ── Budget Variance helpers ───────────────────────────────────────────────────
function varPillClass(pct, isExpense) {
  if (pct === null || pct === undefined) return 'bg-mp-card-hover text-mp-muted border-mp-border'
  const good = isExpense ? pct <= 0 : pct >= 0
  if (good)  return 'bg-mp-success/15 text-mp-success border-mp-success'
  if (Math.abs(pct) < 10) return 'bg-mp-warning/15 text-mp-warning border-mp-warning'
  return 'bg-mp-danger/15 text-mp-danger border-mp-danger'
}
function fmtPct(val) {
  if (val === null || val === undefined) return '—'
  return (val > 0 ? '+' : '') + val.toFixed(1) + '%'
}

// ── Notes Panel ───────────────────────────────────────────────────────────────
const notesPanel = ref({ open: false, company: null, notes: [], loading: false })
const noteForm   = ref({ note: '', action_items: '', priority: 'medium', saving: false })

function openNotesPanel(c) {
  notesPanel.value = { open: true, company: c, notes: [], loading: true }
  noteForm.value   = { note: '', action_items: '', priority: 'medium', saving: false }
  fetchNotes(c.id)
}
function closeNotesPanel() {
  notesPanel.value.open = false
}

async function fetchNotes(companyId) {
  try {
    const res = await apiFetch(`/dashboard/company-notes/${companyId}`)
    const data = await res.json()
    notesPanel.value.notes   = data.notes || []
    notesPanel.value.loading = false
  } catch {
    notesPanel.value.loading = false
  }
}

async function saveNote() {
  if (!noteForm.value.note.trim()) return
  noteForm.value.saving = true
  try {
    const res = await apiFetch(`/dashboard/company-notes/${notesPanel.value.company.id}`, {
      method: 'POST',
      body: JSON.stringify({
        note:         noteForm.value.note,
        action_items: noteForm.value.action_items,
        priority:     noteForm.value.priority,
      }),
    })
    if (!res.ok) {
      const err = await res.json().catch(() => ({}))
      console.error('Save note failed:', res.status, err)
      noteForm.value.saving = false
      return
    }
    noteForm.value = { note: '', action_items: '', priority: 'medium', saving: false }
    await fetchNotes(notesPanel.value.company.id)
    const c = props.companies.find(x => x.id === notesPanel.value.company.id)
    if (c) c.notes_count = notesPanel.value.notes.length
  } catch (e) {
    console.error('Save note error:', e)
    noteForm.value.saving = false
  }
}

async function deleteNote(n) {
  if (!confirm('Delete this note?')) return
  await apiFetch(`/dashboard/company-notes/${notesPanel.value.company.id}/${n.id}`, { method: 'DELETE' })
  await fetchNotes(notesPanel.value.company.id)
  const c = props.companies.find(x => x.id === notesPanel.value.company.id)
  if (c) c.notes_count = notesPanel.value.notes.length
}

function apiFetch(url, options = {}) {
  const token = document.cookie.match(/XSRF-TOKEN=([^;]+)/)?.[1]
  const { headers: extraHeaders, ...restOptions } = options
  return fetch(url, {
    credentials: 'include',
    ...restOptions,
    headers: {
      'Content-Type': 'application/json',
      'X-XSRF-TOKEN': token ? decodeURIComponent(token) : '',
      ...(extraHeaders || {}),
    },
  })
}

// ── Activity helpers ──────────────────────────────────────────────────────────
function activityIcon(type) {
  return { financial_statement: '📋', budget: '📊', kpi: '🎯', sales_upload: '📈', expense_upload: '💸', document: '📁', study: '🔬' }[type] || '📌'
}
function activityIconBg(type) {
  return {
    financial_statement: 'bg-mp-teal-subtle border border-mp-teal',
    budget:              'bg-mp-gold/15 border border-mp-gold',
    kpi:                 'bg-mp-success/15 border border-mp-success',
    sales_upload:        'bg-mp-teal-subtle/30 border border-mp-teal',
    expense_upload:      'bg-mp-gold/15 border border-mp-gold',
    study:               'bg-mp-gold/20 border border-mp-gold',
  }[type] || 'bg-mp-card-hover border border-mp-border'
}

// ── Priority helpers ──────────────────────────────────────────────────────────
function priorityActive(p) {
  return { high: 'bg-mp-danger/15 text-mp-danger border-mp-danger', medium: 'bg-mp-gold/15 text-mp-warning border-mp-gold', low: 'bg-mp-success/15 text-mp-success border-mp-success' }[p]
}
function priorityBadge(p) {
  return { high: 'bg-mp-danger/15 text-mp-danger border-mp-danger', medium: 'bg-mp-gold/15 text-mp-warning border-mp-gold', low: 'bg-mp-success/15 text-mp-success border-mp-success' }[p] || 'bg-mp-card-hover text-mp-muted border-mp-border'
}

// ── Formatters ────────────────────────────────────────────────────────────────
function fmt(val) {
  if (val === null || val === undefined) return '—'
  return Number(val).toLocaleString('en-US', { maximumFractionDigits: 0 })
}
function fmtM(val) {
  if (!val) return '—'
  const n = Number(val)
  if (Math.abs(n) >= 1_000_000_000) return (n / 1_000_000_000).toFixed(2) + 'B'
  if (Math.abs(n) >= 1_000_000)     return (n / 1_000_000).toFixed(2) + 'M'
  if (Math.abs(n) >= 1_000)         return (n / 1_000).toFixed(1) + 'K'
  return n.toFixed(0)
}
function statusBadge(status) {
  return {
    active:   'bg-mp-success/15 text-mp-success border border-mp-success',
    prospect: 'bg-mp-teal-subtle text-white border border-mp-teal',
    exited:   'bg-mp-card-hover text-mp-muted border border-mp-border',
    at_risk:  'bg-mp-danger/15 text-mp-danger border border-mp-danger',
    watch:    'bg-mp-warning/15 text-mp-warning border border-mp-warning',
  }[status] || 'bg-mp-card-hover text-mp-muted border border-mp-border'
}
</script>

<style scoped>
.slide-panel-enter-active,
.slide-panel-leave-active {
  transition: opacity 0.2s ease;
}
.slide-panel-enter-active .relative,
.slide-panel-leave-active .relative {
  transition: transform 0.25s ease;
}
.slide-panel-enter-from { opacity: 0; }
.slide-panel-enter-from .relative { transform: translateX(100%); }
.slide-panel-leave-to { opacity: 0; }
.slide-panel-leave-to .relative { transform: translateX(100%); }
</style>
<template>
  <Head :title="form.type === 'prospect' ? 'Add New Prospect' : 'Add New Investment'" />

  <AuthenticatedLayout>
    <div class="min-h-screen bg-mp-page text-white">

      <!-- PAGE HEADER -->
      <div class="bg-mp-card border-b border-mp-border">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
          <div>
            <Link href="/portfolio-companies"
              class="flex items-center gap-2 text-sm text-white hover:text-white transition-colors mb-2 w-fit">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
              Back to Portfolio
            </Link>
            <h1 class="text-2xl font-bold text-white">
              {{ form.type === 'prospect' ? 'Add New Prospect' : 'Add New Investment' }}
            </h1>
            <p class="text-white text-sm mt-1">
              {{ form.type === 'prospect'
                ? 'Record a deal under evaluation before committing capital'
                : 'Record a new company your firm has invested in' }}
            </p>
          </div>
        </div>
      </div>

      <!-- FORM -->
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <div v-if="$page.props.flash?.success" class="mb-6 bg-mp-success/15 border border-mp-success text-mp-success px-4 py-3 rounded-lg text-sm">
          {{ $page.props.flash.success }}
        </div>

        <form @submit.prevent="submit" class="space-y-8">

          <!-- ── SECTION: TYPE SELECTOR ── -->
          <div class="bg-mp-card rounded-xl border border-mp-border p-6">
            <p class="text-xs font-semibold text-white uppercase tracking-widest mb-4">Entry Type</p>
            <div class="grid grid-cols-2 gap-4">

              <!-- Investment Card -->
              <button
                type="button"
                @click="form.type = 'investment'"
                :class="[
                  'relative flex flex-col items-start gap-2 p-5 rounded-xl border-2 transition-all text-left',
                  form.type === 'investment'
                    ? 'border-mp-teal bg-mp-teal-subtle/40'
                    : 'border-mp-border bg-mp-card-hover hover:border-mp-border'
                ]"
              >
                <div class="flex items-center gap-3 w-full">
                  <span :class="[
                    'w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0',
                    form.type === 'investment' ? 'bg-mp-teal' : 'bg-mp-page'
                  ]">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                  </span>
                  <div>
                    <p class="font-semibold text-white text-sm">Investment</p>
                    <p class="text-xs text-white">Capital already deployed</p>
                  </div>
                  <!-- Checkmark -->
                  <span v-if="form.type === 'investment'" class="ml-auto">
                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                  </span>
                </div>
              </button>

              <!-- Prospect Card -->
              <button
                type="button"
                @click="form.type = 'prospect'"
                :class="[
                  'relative flex flex-col items-start gap-2 p-5 rounded-xl border-2 transition-all text-left',
                  form.type === 'prospect'
                    ? 'border-mp-gold bg-mp-gold/40'
                    : 'border-mp-border bg-mp-card-hover hover:border-mp-border'
                ]"
              >
                <div class="flex items-center gap-3 w-full">
                  <span :class="[
                    'w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0',
                    form.type === 'prospect' ? 'bg-mp-gold-dark' : 'bg-mp-page'
                  ]">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                  </span>
                  <div>
                    <p class="font-semibold text-white text-sm">Prospect</p>
                    <p class="text-xs text-white">Case under evaluation</p>
                  </div>
                  <!-- Checkmark -->
                  <span v-if="form.type === 'prospect'" class="ml-auto">
                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                  </span>
                </div>
              </button>

            </div>
          </div>

          <!-- ── SECTION: ORGANIZATION (super-admin only) ── -->
          <div v-if="isSuperAdmin" class="bg-mp-warning/30 rounded-xl border border-mp-warning/50 p-6">
            <p class="text-xs font-semibold text-mp-warning uppercase tracking-widest mb-2">Organization Assignment</p>
            <p class="text-white text-xs mb-4">As super-admin, select which organization this company belongs to</p>
            <select v-model="form.organization_id"
              class="w-full bg-mp-card-hover border border-mp-border rounded-lg px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-mp-warning focus:border-transparent transition">
              <option value="">— Select Organization —</option>
              <option v-for="org in organizations" :key="org.id" :value="org.id">
                {{ org.name }}
              </option>
            </select>
            <p v-if="form.errors.organization_id" class="text-mp-danger text-xs mt-1">{{ form.errors.organization_id }}</p>
          </div>

          <!-- ── SECTION: COMPANY INFO ── -->
          <div class="bg-mp-card rounded-xl border border-mp-border p-6">
            <p class="text-xs font-semibold text-white uppercase tracking-widest mb-6">Company Information</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

              <!-- Company Name -->
              <div class="md:col-span-1">
                <label class="block text-xs font-semibold text-white uppercase tracking-widest mb-2">Company Name <span class="text-mp-danger">*</span></label>
                <input v-model="form.name" type="text" placeholder="e.g. TechCo Egypt"
                  class="w-full bg-mp-card-hover border border-mp-border rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-mp-teal focus:border-transparent transition" />
                <p v-if="form.errors.name" class="text-mp-danger text-xs mt-1">{{ form.errors.name }}</p>
              </div>

                <!-- Company Phase -->
             <div>
                <label class="block text-xs font-semibold text-white uppercase tracking-widest mb-2">Company Phase <span class="text-mp-danger">*</span>
                </label>
                <select v-model="form.company_phase"
                  class="w-full bg-mp-card-hover border border-mp-border rounded-lg px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-mp-teal focus:border-transparent transition">
                  <option value="">— Select Company Phase —</option>
                  <option
                    v-for="phase in companyPhases"
                    :key="phase.value"
                    :value="phase.value"
                  >
                    {{ phase.name.replace(/([A-Z])/g, ' $1').trim() }}   
                  </option>
                </select>
                <p v-if="form.errors.company_phase" class="text-mp-danger text-xs mt-1">
                  {{ form.errors.company_phase }}
                </p>
              </div>

              <!-- Sector -->
              <div>
                <label class="block text-xs font-semibold text-white uppercase tracking-widest mb-2">Sector <span class="text-mp-danger">*</span></label>
                <input v-model="form.sector" type="text" placeholder="e.g. Technology, Healthcare"
                  class="w-full bg-mp-card-hover border border-mp-border rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-mp-teal focus:border-transparent transition" />
                <p v-if="form.errors.sector" class="text-mp-danger text-xs mt-1">{{ form.errors.sector }}</p>
              </div>

              <!-- Status -->
              <div>
                <label class="block text-xs font-semibold text-white uppercase tracking-widest mb-2">Status <span class="text-mp-danger">*</span></label>
                <select v-model="form.status"
                  class="w-full bg-mp-card-hover border border-mp-border rounded-lg px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-mp-teal focus:border-transparent transition">
                  <option value="on_track">✅ On Track</option>
                  <option value="at_risk">🔴 At Risk</option>
                  <option value="watch">🟡 Watch</option>
                </select>
                <p v-if="form.errors.status" class="text-mp-danger text-xs mt-1">{{ form.errors.status }}</p>
              </div>

              <!-- Transaction Date — label changes based on type -->
              <div>
                <label class="block text-xs font-semibold text-white uppercase tracking-widest mb-2">
                  {{ form.type === 'prospect' ? 'Expected Closing Date' : 'Transaction Date' }}
                  <span class="text-mp-danger">*</span>
                </label>
                <input type="date"
                  :value="form.transaction_date"
                  @change="form.transaction_date = $event.target.value"
                  class="w-full bg-mp-card-hover border border-mp-border rounded-lg px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-mp-teal focus:border-transparent transition" />
                <p v-if="form.errors.transaction_date" class="text-mp-danger text-xs mt-1">{{ form.errors.transaction_date }}</p>
              </div>

              <!-- Last Financial Update -->
              <div>
                <label class="block text-xs font-semibold text-white uppercase tracking-widest mb-2">Last Financial Update</label>
                <input type="date"
                  :value="form.last_financial_update"
                  @change="form.last_financial_update = $event.target.value"
                  class="w-full bg-mp-card-hover border border-mp-border rounded-lg px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-mp-teal focus:border-transparent transition" />
                <p v-if="form.errors.last_financial_update" class="text-mp-danger text-xs mt-1">{{ form.errors.last_financial_update }}</p>
              </div>

            </div>
          </div>

          <!-- ── SECTION: INVESTMENT DETAILS ── -->
          <div class="bg-mp-card rounded-xl border border-mp-border p-6">
            <p class="text-xs font-semibold text-white uppercase tracking-widest mb-6">
              {{ form.type === 'prospect' ? 'Deal Details' : 'Investment Details' }}
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

              <!-- Invested Amount -->
              <div>
                <label class="block text-xs font-semibold text-white uppercase tracking-widest mb-2">
                  {{ form.type === 'prospect' ? 'Estimated Deal Size' : 'Invested Amount' }}
                  <span class="text-mp-danger">*</span>
                </label>
                <input type="text"
                  :value="displayInvestedAmount"
                  @focus="investedFocused = true"
                  @blur="investedFocused = false"
                  @input="form.invested_amount = stripCommas($event.target.value)"
                  placeholder="e.g. 5,000,000"
                  class="w-full bg-mp-card-hover border border-mp-border rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-mp-teal focus:border-transparent transition" />
                <p v-if="form.errors.invested_amount" class="text-mp-danger text-xs mt-1">{{ form.errors.invested_amount }}</p>
              </div>

              <!-- Invested Currency -->
              <div>
                <label class="block text-xs font-semibold text-white uppercase tracking-widest mb-2">Currency <span class="text-mp-danger">*</span></label>
                <select v-model="form.invested_currency"
                  class="w-full bg-mp-card-hover border border-mp-border rounded-lg px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-mp-teal focus:border-transparent transition">
                  <option value="EGP">EGP — Egyptian Pound</option>
                  <option value="USD">USD — US Dollar</option>
                  <option value="EUR">EUR — Euro</option>
                  <option value="GBP">GBP — British Pound</option>
                  <option value="SAR">SAR — Saudi Riyal</option>
                  <option value="AED">AED — UAE Dirham</option>
                </select>
                <p v-if="form.errors.invested_currency" class="text-mp-danger text-xs mt-1">{{ form.errors.invested_currency }}</p>
              </div>

              <!-- FX Reference Currency -->
              <div>
                <label class="block text-xs font-semibold text-white uppercase tracking-widest mb-2">FX Reference Currency <span class="text-mp-danger">*</span></label>
                <select v-model="form.fx_currency"
                  class="w-full bg-mp-card-hover border border-mp-border rounded-lg px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-mp-teal focus:border-transparent transition">
                  <option value="USD">USD — US Dollar</option>
                  <option value="EUR">EUR — Euro</option>
                  <option value="GBP">GBP — British Pound</option>
                  <option value="EGP">EGP — Egyptian Pound</option>
                </select>
                <p v-if="form.errors.fx_currency" class="text-mp-danger text-xs mt-1">{{ form.errors.fx_currency }}</p>
              </div>

              <!-- FX Rate -->
              <div>
                <label class="block text-xs font-semibold text-white uppercase tracking-widest mb-2">FX Rate at Investment Date <span class="text-mp-danger">*</span></label>
                <input type="text"
                  :value="displayFxRate"
                  @focus="fxRateFocused = true"
                  @blur="fxRateFocused = false"
                  @input="form.fx_rate = stripCommas($event.target.value)"
                  placeholder="e.g. 50.75"
                  class="w-full bg-mp-card-hover border border-mp-border rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-mp-teal focus:border-transparent transition" />
                <p v-if="form.errors.fx_rate" class="text-mp-danger text-xs mt-1">{{ form.errors.fx_rate }}</p>
              </div>

              <!-- Equity Stake -->
              <div>
                <label class="block text-xs font-semibold text-white uppercase tracking-widest mb-2">Equity Stake (%) <span class="text-mp-danger">*</span></label>
                <input type="text"
                  :value="displayEquityStake"
                  @focus="equityFocused = true"
                  @blur="equityFocused = false"
                  @input="form.equity_stake = stripCommas($event.target.value)"
                  placeholder="e.g. 40 for 40%"
                  class="w-full bg-mp-card-hover border border-mp-border rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-mp-teal focus:border-transparent transition" />
                <p v-if="form.errors.equity_stake" class="text-mp-danger text-xs mt-1">{{ form.errors.equity_stake }}</p>
              </div>

              <!-- EBITDA Multiplier -->
              <div>
                <label class="block text-xs font-semibold text-white uppercase tracking-widest mb-2">EBITDA Multiplier (x)</label>
                <input type="text"
                  :value="displayEbitda"
                  @focus="ebitdaFocused = true"
                  @blur="ebitdaFocused = false"
                  @input="form.ebitda_multiplier = stripCommas($event.target.value)"
                  placeholder="e.g. 8.5"
                  class="w-full bg-mp-card-hover border border-mp-border rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-mp-teal focus:border-transparent transition" />
                <p v-if="form.errors.ebitda_multiplier" class="text-mp-danger text-xs mt-1">{{ form.errors.ebitda_multiplier }}</p>
              </div>

              <!-- Entry Valuation (auto-calculated) -->
              <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-white uppercase tracking-widest mb-2">Entry Valuation (Auto-Calculated)</label>
                <div class="w-full bg-mp-card-hover border border-mp-teal rounded-lg px-4 py-3 text-white font-semibold">
                  {{ entryValuationDisplay }}
                </div>
                <p class="text-white text-xs mt-1">= Amount ÷ Equity Stake %</p>
              </div>

            </div>
          </div>

          <!-- ── SECTION: PERFORMANCE METRICS ── -->
          <div class="bg-mp-card rounded-xl border border-mp-border p-6">
            <p class="text-xs font-semibold text-white uppercase tracking-widest mb-1">
              Performance Metrics
              <span class="text-white font-normal normal-case tracking-normal">(optional)</span>
            </p>
            <!-- Note for prospects -->
            <p v-if="form.type === 'prospect'" class="text-xs text-white mb-6">
              MOIC and IRR are not required for Prospects — fill in once the deal closes.
            </p>
            <p v-else class="text-white text-xs mb-6">These can be filled in later as the investment matures.</p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

              <!-- Current Valuation -->
              <div>
                <label class="block text-xs font-semibold text-white uppercase tracking-widest mb-2">Current Valuation</label>
                <input type="text"
                  :value="displayCurrentValuation"
                  @focus="currentValFocused = true"
                  @blur="currentValFocused = false"
                  @input="form.current_valuation = stripCommas($event.target.value)"
                  placeholder="e.g. 10,000,000"
                  class="w-full bg-mp-card-hover border border-mp-border rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-mp-teal focus:border-transparent transition" />
                <p v-if="form.errors.current_valuation" class="text-mp-danger text-xs mt-1">{{ form.errors.current_valuation }}</p>
              </div>

              <!-- MOIC -->
              <div>
                <label class="block text-xs font-semibold text-white uppercase tracking-widest mb-2">
                  MOIC (x)
                  <span v-if="form.type === 'prospect'" class="text-white font-normal normal-case tracking-normal">— N/A for Prospects</span>
                </label>
                <input type="text" v-model="form.moic" placeholder="e.g. 2.5"
                  :disabled="form.type === 'prospect'"
                  :class="[
                    'w-full rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-mp-teal focus:border-transparent transition',
                    form.type === 'prospect'
                      ? 'bg-mp-card-hover border border-mp-border opacity-40 cursor-not-allowed'
                      : 'bg-mp-card-hover border border-mp-border'
                  ]" />
                <p v-if="form.errors.moic" class="text-mp-danger text-xs mt-1">{{ form.errors.moic }}</p>
              </div>

              <!-- IRR -->
              <div>
                <label class="block text-xs font-semibold text-white uppercase tracking-widest mb-2">
                  IRR (%)
                  <span v-if="form.type === 'prospect'" class="text-white font-normal normal-case tracking-normal">— N/A for Prospects</span>
                </label>
                <input type="text" v-model="form.irr" placeholder="e.g. 22.5"
                  :disabled="form.type === 'prospect'"
                  :class="[
                    'w-full rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-mp-teal focus:border-transparent transition',
                    form.type === 'prospect'
                      ? 'bg-mp-card-hover border border-mp-border opacity-40 cursor-not-allowed'
                      : 'bg-mp-card-hover border border-mp-border'
                  ]" />
                <p v-if="form.errors.irr" class="text-mp-danger text-xs mt-1">{{ form.errors.irr }}</p>
              </div>

            </div>
          </div>

          <!-- ── SECTION: NOTES ── -->
          <div class="bg-mp-card rounded-xl border border-mp-border p-6">
            <p class="text-xs font-semibold text-white uppercase tracking-widest mb-6">Notes</p>
            <textarea v-model="form.notes" rows="4"
              :placeholder="form.type === 'prospect' ? 'Any notes about this prospect or deal thesis...' : 'Any additional notes about this investment...'"
              class="w-full bg-mp-card-hover border border-mp-border rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-mp-teal focus:border-transparent transition resize-none"></textarea>
            <p v-if="form.errors.notes" class="text-mp-danger text-xs mt-1">{{ form.errors.notes }}</p>
          </div>

          <!-- ── SUBMIT ── -->
          <div class="flex items-center justify-end gap-4 pb-8">
            <Link href="/portfolio-companies"
              class="px-6 py-3 rounded-lg border border-mp-border text-white hover:text-white hover:border-mp-border text-sm font-medium transition-colors">
              Cancel
            </Link>
            <button type="submit" :disabled="form.processing"
              :class="[
                'flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-medium px-8 py-3 rounded-lg transition-colors',
                form.type === 'prospect' ? 'bg-mp-gold-dark hover:bg-mp-gold' : 'bg-mp-teal hover:bg-mp-teal-dark'
              ]">
              <svg v-if="form.processing" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
              </svg>
              {{ form.processing ? 'Saving...' : (form.type === 'prospect' ? 'Save Prospect' : 'Save Investment') }}
            </button>
          </div>

        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
  organizations: { type: Array,  default: () => [] },
  defaultOrgId:  { type: Number, default: null },
  companyPhases: { type: Array,  default: () => [] },   // already there
})

const page = usePage()
const isSuperAdmin = computed(() =>
  Object.values(page.props.auth.user.roles ?? {}).includes('super-admin')
)

function stripCommas(val) {
  if (val === null || val === undefined) return ''
  return String(val).replace(/,/g, '')
}

function addCommas(val) {
  if (val === null || val === undefined || val === '') return ''
  const clean = stripCommas(String(val))
  if (isNaN(clean)) return clean
  const parts = clean.split('.')
  parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',')
  return parts.join('.')
}

const form = useForm({
  type:                  'investment',
  organization_id:       props.defaultOrgId ?? '',
  name:                  '',
  company_phase:         '',           // ← ADD THIS LINE
  sector:                '',
  status:                'on_track',
  transaction_date:      '',
  last_financial_update: '',
  invested_amount:       '',
  invested_currency:     'EGP',
  fx_currency:           'USD',
  fx_rate:               '',
  equity_stake:          '',
  ebitda_multiplier:     '',
  current_valuation:     '',
  moic:                  '',
  irr:                   '',
  notes:                 '',
})

const investedFocused   = ref(false)
const fxRateFocused     = ref(false)
const equityFocused     = ref(false)
const ebitdaFocused     = ref(false)
const currentValFocused = ref(false)

const displayInvestedAmount   = computed(() => investedFocused.value   ? stripCommas(form.invested_amount)   : addCommas(form.invested_amount))
const displayFxRate           = computed(() => fxRateFocused.value     ? stripCommas(form.fx_rate)           : addCommas(form.fx_rate))
const displayEquityStake      = computed(() => equityFocused.value     ? stripCommas(form.equity_stake)      : addCommas(form.equity_stake))
const displayEbitda           = computed(() => ebitdaFocused.value     ? stripCommas(form.ebitda_multiplier) : addCommas(form.ebitda_multiplier))
const displayCurrentValuation = computed(() => currentValFocused.value ? stripCommas(form.current_valuation) : addCommas(form.current_valuation))

const entryValuationDisplay = computed(() => {
  const amount = parseFloat(stripCommas(form.invested_amount))
  const equity = parseFloat(stripCommas(form.equity_stake))
  if (!amount || !equity || equity === 0) return '—'
  return addCommas((amount / (equity / 100)).toFixed(0))
})

function submit() {
  // Convert empty string to null (because enum is nullable)
  if (form.company_phase === '') {
    form.company_phase = null
  }

  if (form.type === 'prospect') {
    form.moic = ''
    form.irr  = ''
  }
  form.post('/portfolio-companies')
}
</script>
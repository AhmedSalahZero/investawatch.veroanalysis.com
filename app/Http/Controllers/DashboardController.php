<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\PortfolioCompany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user  = auth()->user();
        $orgId = $user->organization_id;
    
        if (!$user->hasRole('super-admin') && !$user->hasRole('admin')) {
            return redirect()->route('portfolio-companies.index');
        }
    
        // ── 1. Super-admin can switch orgs via ?org_id=X, others locked to own org ──
        if ($user->hasRole('super-admin') && request('org_id')) {
            $viewOrgId = (int) request('org_id');
        } else {
            $viewOrgId = $orgId;
        }
    
        $companyRows = \App\Models\PortfolioCompany::where('organization_id', $viewOrgId)->get();
        $companyIds  = $companyRows->pluck('id')->toArray();
    
        // ── 2. Latest Financial Statement figures per company ──────────────────
        $latestFsPeriods = \Illuminate\Support\Facades\DB::table('financial_statements')
            ->whereIn('portfolio_company_id', $companyIds)
            ->select('portfolio_company_id', \Illuminate\Support\Facades\DB::raw('MAX(period_to) as max_period'))
            ->groupBy('portfolio_company_id')
            ->pluck('max_period', 'portfolio_company_id')
            ->toArray();
    
        $fsDataMap = [];
        foreach ($latestFsPeriods as $cid => $maxPeriod) {
            $stmt = \Illuminate\Support\Facades\DB::table('financial_statements')
                ->where('portfolio_company_id', $cid)
                ->where('period_to', $maxPeriod)
                ->first();
            if (!$stmt) continue;
    
            $sections = \Illuminate\Support\Facades\DB::table('fs_sections')
                ->where('financial_statement_id', $stmt->id)
                ->where('statement_type', 'income')
                ->get()->keyBy('section_key');
    
            $sumSection = function ($key) use ($sections) {
                if (!isset($sections[$key])) return 0.0;
                return (float) \Illuminate\Support\Facades\DB::table('fs_line_items')
                    ->where('fs_section_id', $sections[$key]->id)
                    ->sum('amount');
            };
    
            $salesRev  = $sumSection('sales_revenue');
            $cogs      = $sumSection('cogs');
            $marketing = $sumSection('marketing_expenses');
            $ga        = $sumSection('ga_expenses');
            $finCosts  = $sumSection('finance_costs');
            $taxes     = $sumSection('taxes');
            $netProfit = ($salesRev - $cogs - $marketing - $ga - $finCosts) - $taxes;
    
            $fsDataMap[$cid] = [
                'last_revenue'    => $salesRev ?: null,
                'last_net_profit' => $netProfit,
                'last_fs_period'  => \Carbon\Carbon::parse($maxPeriod)->format('M Y'),
            ];
        }
    
        // ── 3. KPI status per company ──────────────────────────────────────────
        $kpiRows = \Illuminate\Support\Facades\DB::table('kpi_trackings as kt')
            ->join('kpi_definitions as kd', 'kd.id', '=', 'kt.kpi_definition_id')
            ->whereIn('kt.company_id', $companyIds)
            ->select('kt.company_id', 'kd.name as kpi_name', 'kd.higher_is_better',
                     'kt.actual', 'kt.target', 'kt.period_label')
            ->orderByDesc('kt.period_label')
            ->get();
    
        $latestKpiMap = [];
        foreach ($kpiRows as $row) {
            $key = $row->company_id . '_' . $row->kpi_name;
            if (!isset($latestKpiMap[$key])) $latestKpiMap[$key] = $row;
        }
    
        $kpiStatusMap = [];
        $atRiskKpis   = [];
    
        foreach ($latestKpiMap as $row) {
            $cid = $row->company_id;
            if (!isset($kpiStatusMap[$cid])) $kpiStatusMap[$cid] = ['on_track' => 0, 'at_risk' => 0];
            if ($row->actual === null || $row->target === null || (float) $row->target == 0) continue;
    
            $actual  = (float) $row->actual;
            $target  = (float) $row->target;
            $pct     = round(($actual - $target) / abs($target) * 100, 1);
            $higher  = $row->higher_is_better ?? true;
            $onTrack = $higher ? ($actual >= $target * 0.9) : ($actual <= $target * 1.1);
            $status  = $onTrack ? 'on_track'
                : ($higher ? ($actual >= $target * 0.75 ? 'watch' : 'at_risk')
                           : ($actual <= $target * 1.25  ? 'watch' : 'at_risk'));
    
            if ($status === 'on_track') {
                $kpiStatusMap[$cid]['on_track']++;
            } else {
                $kpiStatusMap[$cid]['at_risk']++;
                $atRiskKpis[] = [
                    'company_id'   => $cid,
                    'company_name' => $companyRows->firstWhere('id', $cid)?->name ?? '—',
                    'kpi_name'     => $row->kpi_name,
                    'target'       => $target,
                    'actual'       => $actual,
                    'variance_pct' => $pct,
                    'status'       => $status,
                ];
            }
        }
    
        // ── 4. Recent Activity ─────────────────────────────────────────────────
        $fsActivity = \Illuminate\Support\Facades\DB::table('financial_statements as fs')
            ->join('portfolio_companies as pc', 'pc.id', '=', 'fs.portfolio_company_id')
            ->whereIn('fs.portfolio_company_id', $companyIds)
            ->selectRaw("pc.id as company_id, pc.name as company_name, fs.created_at,
                         'financial_statement' as type,
                         CONCAT('Financial statement added (', DATE_FORMAT(fs.period_from,'%b %Y'), ' \xe2\x80\x93 ', DATE_FORMAT(fs.period_to,'%b %Y'), ')') as description,
                         CONCAT('/portfolio-companies/', pc.id, '/financial-statements') as link")
            ->orderByDesc('fs.created_at')->limit(6)->get()->toArray();
    
        $salesActivity = \Illuminate\Support\Facades\DB::table('sales_uploads as su')
            ->join('portfolio_companies as pc', 'pc.id', '=', 'su.portfolio_company_id')
            ->whereIn('su.portfolio_company_id', $companyIds)
            ->selectRaw("pc.id as company_id, pc.name as company_name, su.created_at,
                         'sales_upload' as type,
                         'Sales data uploaded' as description,
                         CONCAT('/companies/', pc.id, '/sales') as link")
            ->orderByDesc('su.created_at')->limit(6)->get()->toArray();
    
        $budgetActivity = \Illuminate\Support\Facades\DB::table('budget_statements as bs')
            ->join('portfolio_companies as pc', 'pc.id', '=', 'bs.portfolio_company_id')
            ->whereIn('bs.portfolio_company_id', $companyIds)
            ->selectRaw("pc.id as company_id, pc.name as company_name, bs.created_at,
                         'budget' as type,
                         CONCAT('Budget created: ', bs.name) as description,
                         CONCAT('/portfolio-companies/', pc.id, '/budgets') as link")
            ->orderByDesc('bs.created_at')->limit(6)->get()->toArray();
    
        $allActivity = array_merge($fsActivity, $salesActivity, $budgetActivity);
        usort($allActivity, fn($a, $b) => strcmp(
            is_object($b) ? $b->created_at : $b['created_at'],
            is_object($a) ? $a->created_at : $a['created_at']
        ));
    
        $recentActivity = array_map(function ($r) {
            $r = (object) $r;
            return [
                'company_id'   => $r->company_id,
                'company_name' => $r->company_name,
                'type'         => $r->type,
                'description'  => $r->description,
                'link'         => $r->link,
                'created_at'   => \Carbon\Carbon::parse($r->created_at)->diffForHumans(),
            ];
        }, array_slice($allActivity, 0, 20));
    
        // ── 5. Budget Variance per company (latest budget for current year) ─────
        $currentYear = (int) date('Y');
    
        $latestBudgets = \Illuminate\Support\Facades\DB::table('budget_statements')
            ->whereIn('portfolio_company_id', $companyIds)
            ->where('year', $currentYear)
            ->orderByDesc('created_at')
            ->get()
            ->unique('portfolio_company_id')
            ->keyBy('portfolio_company_id');
    
        $computedFormulas = [
            'gross_profit' => [['key'=>'sales_revenue','sign'=>1], ['key'=>'cogs','sign'=>-1]],
            'ebitda'       => [['key'=>'gross_profit','sign'=>1],  ['key'=>'marketing_expenses','sign'=>-1], ['key'=>'ga_expenses','sign'=>-1]],
            'ebit'         => [['key'=>'ebitda','sign'=>1],        ['key'=>'depreciation','sign'=>-1]],
            'ebt'          => [['key'=>'ebit','sign'=>1],          ['key'=>'finance_income_expense','sign'=>1]],
            'net_profit'   => [['key'=>'ebt','sign'=>1],           ['key'=>'taxes','sign'=>-1]],
        ];
    
        $ytdMonths = (int) date('n');
    
        $sumYtd = function ($jsonStr) use ($ytdMonths) {
            $data = json_decode($jsonStr ?? '{}', true) ?? [];
            $total = 0.0;
            for ($m = 1; $m <= $ytdMonths; $m++) {
                $total += (float) ($data[$m] ?? $data[(string)$m] ?? 0);
            }
            return $total;
        };
    
        $budgetMap = [];
    
        foreach ($latestBudgets as $cid => $bgt) {
            $sections = \Illuminate\Support\Facades\DB::table('budget_sections')
                ->where('budget_statement_id', $bgt->id)
                ->where('statement_type', 'income')
                ->get();
    
            $secBudget = [];
            $secActual = [];

            $sectionIds = $sections->pluck('id');
            $lineItemsBySection = $sectionIds->isEmpty()
                ? collect()
                : \Illuminate\Support\Facades\DB::table('budget_groups as bg')
                    ->join('budget_line_items as bli', 'bli.budget_group_id', '=', 'bg.id')
                    ->whereIn('bg.budget_section_id', $sectionIds)
                    ->get(['bg.budget_section_id as section_id', 'bli.id as line_item_id', 'bli.monthly_amounts'])
                    ->groupBy('section_id');

            $allLiIds = $lineItemsBySection->flatten(1)->pluck('line_item_id');
            $actualsByLi = $allLiIds->isEmpty()
                ? collect()
                : \Illuminate\Support\Facades\DB::table('budget_actuals')
                    ->whereIn('budget_line_item_id', $allLiIds)
                    ->get(['budget_line_item_id', 'monthly_actuals'])
                    ->keyBy('budget_line_item_id');
    
            foreach ($sections as $sec) {
                $items = $lineItemsBySection->get($sec->id, collect());
                $secBudgetTotal = 0.0;
                $secActualTotal = 0.0;
                foreach ($items as $li) {
                    $secBudgetTotal += $sumYtd($li->monthly_amounts);
                    $act = $actualsByLi->get($li->line_item_id);
                    if ($act) {
                        $secActualTotal += $sumYtd($act->monthly_actuals);
                    }
                }
                $secBudget[$sec->section_key] = $secBudgetTotal;
                $secActual[$sec->section_key] = $secActualTotal;
            }
    
            for ($pass = 0; $pass < 5; $pass++) {
                foreach ($computedFormulas as $key => $formula) {
                    if (isset($secBudget[$key])) continue;
                    $bVal = 0.0; $aVal = 0.0; $ok = true;
                    foreach ($formula as $part) {
                        if (!array_key_exists($part['key'], $secBudget)) { $ok = false; break; }
                        $bVal += $secBudget[$part['key']] * $part['sign'];
                        $aVal += $secActual[$part['key']] * $part['sign'];
                    }
                    if ($ok) { $secBudget[$key] = $bVal; $secActual[$key] = $aVal; }
                }
            }
    
            $plRows = [
                ['key' => 'sales_revenue', 'label' => 'Revenue',     'is_expense' => false],
                ['key' => 'cogs',          'label' => 'COGS',         'is_expense' => true],
                ['key' => 'gross_profit',  'label' => 'Gross Profit', 'is_expense' => false],
                ['key' => 'ebitda',        'label' => 'EBITDA',       'is_expense' => false],
                ['key' => 'ebit',          'label' => 'EBIT',         'is_expense' => false],
                ['key' => 'ebt',           'label' => 'EBT',          'is_expense' => false],
                ['key' => 'net_profit',    'label' => 'Net Profit',   'is_expense' => false],
            ];
    
            $plVariance = [];
            foreach ($plRows as $row) {
                $budget   = $secBudget[$row['key']] ?? null;
                $actual   = $secActual[$row['key']] ?? null;
                $variance = ($budget !== null && $actual !== null) ? $actual - $budget : null;
                $plVariance[] = [
                    'key'        => $row['key'],
                    'label'      => $row['label'],
                    'is_expense' => $row['is_expense'],
                    'budget'     => $budget,
                    'actual'     => $actual,
                    'variance'   => $variance,
                    'var_pct'    => ($budget && $budget != 0)
                                    ? round(($variance / abs($budget)) * 100, 1)
                                    : null,
                ];
            }
    
            $budgetMap[$cid] = [
                'budget_id'         => $bgt->id,
                'budget_name'       => $bgt->name,
                'budget_ytd_months' => $ytdMonths,
                'budget_variance'   => $plVariance,
            ];
        }
    
        // ── 6. Notes count per company ─────────────────────────────────────────
        $notesCountMap = \Illuminate\Support\Facades\DB::table('portfolio_company_notes')
            ->whereIn('portfolio_company_id', $companyIds)
            ->selectRaw('portfolio_company_id, COUNT(*) as cnt')
            ->groupBy('portfolio_company_id')
            ->pluck('cnt', 'portfolio_company_id')
            ->toArray();
    
        // ── 7. Build final companies array ─────────────────────────────────────
        $companies = $companyRows->map(function ($c) use ($fsDataMap, $kpiStatusMap, $budgetMap, $notesCountMap) {
            $fs     = $fsDataMap[$c->id]    ?? [];
            $kpi    = $kpiStatusMap[$c->id] ?? ['on_track' => 0, 'at_risk' => 0];
            $budget = $budgetMap[$c->id]    ?? [];
            return [
                'id'                 => $c->id,
                'type'               => $c->type ?? 'investment',
                'name'               => $c->name,
                'sector'             => $c->sector,
                'status'             => $c->status,
                'logo'               => $c->logo,
                'invested_amount'    => (float) $c->invested_amount,
                'invested_currency'  => $c->invested_currency,
                'equity_stake'       => (float) $c->equity_stake,
                'entry_valuation'    => (float) $c->entry_valuation,
                'current_valuation'  => $c->current_valuation ? (float) $c->current_valuation : null,
                'moic'               => $c->moic ? (float) $c->moic : null,
                'irr'                => $c->irr  ? (float) $c->irr  : null,
                'last_revenue'       => $fs['last_revenue']    ?? null,
                'last_net_profit'    => $fs['last_net_profit'] ?? null,
                'last_fs_period'     => $fs['last_fs_period']  ?? null,
                'kpi_on_track'       => $kpi['on_track'],
                'kpi_at_risk'        => $kpi['at_risk'],
                'budget_id'          => $budget['budget_id']         ?? null,
                'budget_name'        => $budget['budget_name']        ?? null,
                'budget_ytd_months'  => $budget['budget_ytd_months']  ?? null,
                'budget_variance'    => $budget['budget_variance']    ?? null,
                'notes_count'        => (int) ($notesCountMap[$c->id] ?? 0),
            ];
        });
    
        // ── 8. Org info + org list for super-admin switcher ────────────────────
        $org = ['name' => \App\Models\Organization::find($viewOrgId)?->name ?? 'Organization'];
    
        $allOrgs = $user->hasRole('super-admin')
            ? \App\Models\Organization::orderBy('name')->get()
                ->map(fn($o) => ['id' => $o->id, 'name' => $o->name])->toArray()
            : [];
    
        return Inertia::render('Dashboard', [
            'companies'      => $companies,
            'atRiskKpis'     => $atRiskKpis,
            'recentActivity' => $recentActivity,
            'org'            => $org,
            'allOrgs'        => $allOrgs,
            'currentOrgId'   => $viewOrgId,
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InvestmentPlan;
use App\Services\InvestmentService;

class InvestmentController extends Controller
{
    protected $investmentService;

    public function __construct(InvestmentService $investmentService)
    {
        $this->investmentService = $investmentService;
    }

    /**
     * Lista planos de investimento disponíveis
     * 
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $plans = InvestmentPlan::where('is_active', true)
            ->orderBy('min_amount')
            ->get();

        $userWalleta = auth()->user()->wallets;
        return view('investments.index', compact('plans', 'userWallet'));
    }

     public function store(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:investment_plans,id',
            'amount' => 'required|numeric|min:0',
        ]);
        try {
            $plan = InvestmentPlan::findOrFail($request->plan_id);
            
            $investment = $this->investmentService->createInvestment(
                auth()->user(),
                $plan,
                $request->amount
            );
            return redirect()
                ->route('dashboard')
                ->with('success', 'Investment created successfully! Your trading bot is ready to activate.');
        } catch (\Exception $e) {
            return back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }


     /**
     * Exibe detalhes de um investimento
     * 
     * @param int $id
    * @return \Illuminate\View\View
    */
    public function show($id)
    {
        $investment = auth()->user()->investments()->with(['investmentPlan', 'botInstance'])->findOrFail($id);
        $profitHistory = $investment->transactions()->where('type', 'profit')->orderByDesc('created_at')->limit(30)->get();
        
        return view('investments.show', compact('investment', 'profitHistory'));
    }

}

<?php

namespace App\Livewire;

use App\Models\Investment;
use Livewire\Component;

class PaymentStatus extends Component
{
    public $investment;
    public $status;
    public $timeLeft;
    public $expired = false;

    protected $listeners = ['payment-confirmed' => 'handlePaymentConfirmed'];

    public function mount(Investment $investment)
    {
        $this->investment = $investment;
        $this->status = $investment->payment_status;
        $this->calculateTimeLeft();
    }

    public function checkStatus()
    {
        $this->investment->refresh();
        $this->status = $this->investment->payment_status;

        if ($this->status === 'paid') {
            $this->dispatch('payment-confirmed');
            return redirect()->route('investments.show', $this->investment)
                ->with('success', 'Payment confirmed! Your trading bot is now active.');
        }

        $this->calculateTimeLeft();

        if ($this->timeLeft <= 0 && $this->status === 'pending') {
            $this->expired = true;
            $this->investment->update(['payment_status' => 'expired']);
        }
    }

    protected function calculateTimeLeft()
    {
        if (!isset($this->investment->payment_data['expires_at'])) {
            $this->timeLeft = 1800; // 30 min padrão
            return;
        }

        $expiresAt = \Carbon\Carbon::parse($this->investment->payment_data['expires_at']);
        $this->timeLeft = max(0, now()->diffInSeconds($expiresAt, false));
    }

    public function getFormattedTimeProperty()
    {
        $minutes = floor($this->timeLeft / 60);
        $seconds = $this->timeLeft % 60;
        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    public function render()
    {
        return view('livewire.payment-status');
    }
}
<?php

namespace App\Livewire\Profile;

use App\Services\Sspr\ActiveDirectoryPasswordBroker;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Throwable;

#[Layout('components.layouts.app')]
class Dashboard extends Component
{
    public array $profile = [];

    public ?string $status = null;

    public function mount(ActiveDirectoryPasswordBroker $activeDirectory): void
    {
        $this->refreshProfile($activeDirectory);
    }

    public function refreshProfile(ActiveDirectoryPasswordBroker $activeDirectory): void
    {
        $user = Auth::user();

        try {
            $this->profile = $activeDirectory->profile((string) $user->ad_dn);

            $user->update([
                'name' => $this->profile['display_name'],
                'email' => $this->profile['email'] ?: $user->email,
                'ad_username' => $this->profile['username'],
                'department' => $this->profile['department'],
                'title' => $this->profile['title'],
                'phone' => $this->profile['phone'],
            ]);

            $this->status = 'Profile synchronized from Active Directory.';
        } catch (Throwable $exception) {
            report($exception);

            $this->profile = [
                'display_name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'department' => $user->department,
                'title' => $user->title,
                'username' => $user->ad_username,
            ];
            $this->status = 'Showing the last synchronized profile. Active Directory could not be reached.';
        }
    }

    public function render()
    {
        return view('livewire.profile.dashboard');
    }
}

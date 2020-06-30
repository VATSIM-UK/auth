<?php

namespace App\Models\Concerns;

use App\Constants\BanTypeConstants;
use App\Events\User\Banned;
use App\Exceptions\Ban\AlreadyNetworkBannedException;
use App\Exceptions\Ban\BanEndsBeforeStartException;
use App\Models\Ban;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasBans
{
    /**
     * Gets all present and historic bans.
     *
     * @return HasMany
     */
    public function bans(): HasMany
    {
        return $this->hasMany(Ban::class)->orderBy('starts_at');
    }

    /**
     * Gets all currently active bans.
     *
     * @return HasMany
     */
    public function currentBans(): HasMany
    {
        return $this->bans()
            ->where('starts_at', '<=', Carbon::now())
            ->where(function (Builder $query) {
                return $query->active();
            })
            ->whereNull('repealed_at');
    }

    /**
     * Gets the current network ban.
     *
     * @return Ban|null
     */
    public function getNetworkBanAttribute(): ?Ban
    {
        return $this->currentBans()->network()->first();
    }

    /**
     * Returns whether the user is banned or not.
     *
     * @return bool
     */
    public function getBannedAttribute(): bool
    {
        return $this->currentBans()->count() > 0;
    }

    /**
     * Ban the user locally.
     *
     * @param $body
     * @param Ban\Reason $reason
     * @param User|int $banner
     * @param Carbon $end
     * @return Ban
     * @throws BanEndsBeforeStartException
     */
    public function banLocally($body, Ban\Reason $reason = null, $banner = null, Carbon $end = null): Ban
    {
        return $this->ban(BanTypeConstants::LOCAL, $body, $reason, $banner, $end);
    }

    /**
     * Adds a network ban for the user (only affects status on local system).
     *
     * @param null $body
     * @return Ban
     * @throws AlreadyNetworkBannedException
     * @throws BanEndsBeforeStartException
     */
    public function banNetwork($body = null): Ban
    {
        if ($this->network_ban) {
            throw new AlreadyNetworkBannedException();
        }

        return $this->ban(BanTypeConstants::NETWORK, $body ? $body : 'Network Ban Discovered');
    }

    /**
     * Ends any current network back.
     *
     * @return Ban|null
     */
    public function endNetworkBanIfHas(): ?Ban
    {
        if (! $ban = $this->network_ban) {
            return null;
        }
        $ban->end();

        return $ban;
    }

    /**
     * Bans the user.
     *
     * @param $type int A BanConstant type
     * @param $body string
     * @param Ban\Reason $reason
     * @param User|int $banner
     * @param Carbon|null $end
     * @return Ban
     * @throws BanEndsBeforeStartException
     */
    private function ban($type, $body, Ban\Reason $reason = null, $banner = null, Carbon $end = null): Ban
    {
        // Compose ban
        $ban = new Ban();
        $ban->type = $type;
        $ban->body = $body;

        if ($banner) {
            $ban->banner()->associate($banner);
        }

        $ban->starts_at = Carbon::now();
        $ban->ends_at = $end;

        if ($reason) {
            $ban->reason()->associate($reason);
            if (! $end) {
                // Calculate end time from reason
                $ban->ends_at = Carbon::now()->add($reason->periodInterval);
            }
        }

        // Check end after start
        if ($ban->ends_at && $ban->ends_at->lessThanOrEqualTo($ban->starts_at)) {
            throw new BanEndsBeforeStartException();
        }

        $ban->user()->associate($this);
        $ban->save();

        event(new Banned($ban));

        return $ban;
    }
}

<?php

namespace App\Models;

use Carbon\Carbon;

class MyAccountDetail
{
    public function __construct(
        public readonly string $username,
        public readonly string $email,
        public readonly bool $member,
        public readonly Carbon $member_expiration,
        public readonly string $status,
        public readonly array $badges,
        public readonly array $skins,
        public readonly int $gems,
        public readonly int $event_token,
        public readonly int $achievements_points,
        public readonly bool $banned,
        public readonly string $ban_reason,
    ) {}

    /**
     * @param array $data
     *
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            username: $data['username'] ?? '',
            email: $data['email'] ?? '',
            member: $data['member'] ?? false,
            member_expiration: Carbon::parse($data['member_expiration'] ?? Carbon::now()),
            status: $data['status'] ?? '',
            badges: $data['badges'] ?? [],
            skins: $data['skins'] ?? [],
            gems: $data['gems'] ?? 0,
            event_token: $data['event_token'] ?? 0,
            achievements_points: $data['achievements_points'] ?? 0,
            banned: $data['banned'] ?? false,
            ban_reason: $data['ban_reason'] ?? '',
        );
    }
}

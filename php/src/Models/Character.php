<?php

namespace App\Models;

use Illuminate\Support\Collection;

class Character
{
    // Path parameter options
    const CHARACTER_NAME = 'name';

    public function __construct(
        public readonly string $name,
        public readonly string $account,
        public readonly string $skin,
        public readonly int $level,
        public readonly int $xp,
        public readonly int $max_xp,
        public readonly int $gold,
        public readonly int $speed,
        public readonly int $mining_level,
        public readonly int $mining_xp,
        public readonly int $mining_max_xp,
        public readonly int $woodcutting_level,
        public readonly int $woodcutting_xp,
        public readonly int $woodcutting_max_xp,
        public readonly int $fishing_level,
        public readonly int $fishing_xp,
        public readonly int $fishing_max_xp,
        public readonly int $weaponcrafting_level,
        public readonly int $weaponcrafting_xp,
        public readonly int $weaponcrafting_max_xp,
        public readonly int $gearcrafting_level,
        public readonly int $gearcrafting_xp,
        public readonly int $gearcrafting_max_xp,
        public readonly int $jewelrycrafting_level,
        public readonly int $jewelrycrafting_xp,
        public readonly int $jewelrycrafting_max_xp,
        public readonly int $cooking_level,
        public readonly int $cooking_xp,
        public readonly int $cooking_max_xp,
        public readonly int $alchemy_level,
        public readonly int $alchemy_xp,
        public readonly int $alchemy_max_xp,
        public readonly int $hp,
        public readonly int $max_hp,
        public readonly int $haste,
        public readonly int $critical_strike,
        public readonly int $wisdom,
        public readonly int $prospecting,
        public readonly int $initiative,
        public readonly int $threat,
        public readonly int $attack_fire,
        public readonly int $attack_earth,
        public readonly int $attack_water,
        public readonly int $attack_air,
        public readonly int $dmg,
        public readonly int $dmg_fire,
        public readonly int $dmg_earth,
        public readonly int $dmg_water,
        public readonly int $dmg_air,
        public readonly int $res_fire,
        public readonly int $res_earth,
        public readonly int $res_water,
        public readonly int $res_air,
        public readonly array $effects,
        public readonly int $x,
        public readonly int $y,
        public readonly string $layer,
        public readonly int $map_id,
        public readonly int $cooldown,
        public readonly string $cooldown_expiration,
        public readonly string $weapon_slot,
        public readonly string $rune_slot,
        public readonly string $shield_slot,
        public readonly string $helmet_slot,
        public readonly string $body_armor_slot,
        public readonly string $leg_armor_slot,
        public readonly string $boots_slot,
        public readonly string $ring1_slot,
        public readonly string $ring2_slot,
        public readonly string $amulet_slot,
        public readonly string $artifact1_slot,
        public readonly string $artifact2_slot,
        public readonly string $artifact3_slot,
        public readonly string $utility1_slot,
        public readonly int $utility1_slot_quantity,
        public readonly string $utility2_slot,
        public readonly int $utility2_slot_quantity,
        public readonly string $bag_slot,
        public readonly string $task,
        public readonly string $task_type,
        public readonly int $task_progress,
        public readonly int $task_total,
        public readonly int $inventory_max_items,
        public readonly Collection $inventory,
    ) {}

    /**
     * @param array $data
     *
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? '',
            account: $data['account'] ?? '',
            skin: $data['skin'] ?? '',
            level: $data['level'] ?? 0,
            xp: $data['xp'] ?? 0,
            max_xp: $data['max_xp'] ?? 0,
            gold: $data['gold'] ?? 0,
            speed: $data['speed'] ?? 0,
            mining_level: $data['mining_level'] ?? 0,
            mining_xp: $data['mining_xp'] ?? 0,
            mining_max_xp: $data['mining_max_xp'] ?? 0,
            woodcutting_level: $data['woodcutting_level'] ?? 0,
            woodcutting_xp: $data['woodcutting_xp'] ?? 0,
            woodcutting_max_xp: $data['woodcutting_max_xp'] ?? 0,
            fishing_level: $data['fishing_level'] ?? 0,
            fishing_xp: $data['fishing_xp'] ?? 0,
            fishing_max_xp: $data['fishing_max_xp'] ?? 0,
            weaponcrafting_level: $data['weaponcrafting_level'] ?? 0,
            weaponcrafting_xp: $data['weaponcrafting_xp'] ?? 0,
            weaponcrafting_max_xp: $data['weaponcrafting_max_xp'] ?? 0,
            gearcrafting_level: $data['gearcrafting_level'] ?? 0,
            gearcrafting_xp: $data['gearcrafting_xp'] ?? 0,
            gearcrafting_max_xp: $data['gearcrafting_max_xp'] ?? 0,
            jewelrycrafting_level: $data['jewelrycrafting_level'] ?? 0,
            jewelrycrafting_xp: $data['jewelrycrafting_xp'] ?? 0,
            jewelrycrafting_max_xp: $data['jewelrycrafting_max_xp'] ?? 0,
            cooking_level: $data['cooking_level'] ?? 0,
            cooking_xp: $data['cooking_xp'] ?? 0,
            cooking_max_xp: $data['cooking_max_xp'] ?? 0,
            alchemy_level: $data['alchemy_level'] ?? 0,
            alchemy_xp: $data['alchemy_xp'] ?? 0,
            alchemy_max_xp: $data['alchemy_max_xp'] ?? 0,
            hp: $data['hp'] ?? 0,
            max_hp: $data['max_hp'] ?? 0,
            haste: $data['haste'] ?? 0,
            critical_strike: $data['critical_strike'] ?? 0,
            wisdom: $data['wisdom'] ?? 0,
            prospecting: $data['prospecting'] ?? 0,
            initiative: $data['initiative'] ?? 0,
            threat: $data['threat'] ?? 0,
            attack_fire: $data['attack_fire'] ?? 0,
            attack_earth: $data['attack_earth'] ?? 0,
            attack_water: $data['attack_water'] ?? 0,
            attack_air: $data['attack_air'] ?? 0,
            dmg: $data['dmg'] ?? 0,
            dmg_fire: $data['dmg_fire'] ?? 0,
            dmg_earth: $data['dmg_earth'] ?? 0,
            dmg_water: $data['dmg_water'] ?? 0,
            dmg_air: $data['dmg_air'] ?? 0,
            res_fire: $data['res_fire'] ?? 0,
            res_earth: $data['res_earth'] ?? 0,
            res_water: $data['res_water'] ?? 0,
            res_air: $data['res_air'] ?? 0,
            effects: $data['effects'] ?? [],
            x: $data['x'] ?? 0,
            y: $data['y'] ?? 0,
            layer: $data['layer'] ?? '',
            map_id: $data['map_id'] ?? 0,
            cooldown: $data['cooldown'] ?? 0,
            cooldown_expiration: $data['cooldown_expiration'] ?? '',
            weapon_slot: $data['weapon_slot'] ?? '',
            rune_slot: $data['rune_slot'] ?? '',
            shield_slot: $data['shield_slot'] ?? '',
            helmet_slot: $data['helmet_slot'] ?? '',
            body_armor_slot: $data['body_armor_slot'] ?? '',
            leg_armor_slot: $data['leg_armor_slot'] ?? '',
            boots_slot: $data['boots_slot'] ?? '',
            ring1_slot: $data['ring1_slot'] ?? '',
            ring2_slot: $data['ring2_slot'] ?? '',
            amulet_slot: $data['amulet_slot'] ?? '',
            artifact1_slot: $data['artifact1_slot'] ?? '',
            artifact2_slot: $data['artifact2_slot'] ?? '',
            artifact3_slot: $data['artifact3_slot'] ?? '',
            utility1_slot: $data['utility1_slot'] ?? '',
            utility1_slot_quantity: $data['utility1_slot_quantity'] ?? 0,
            utility2_slot: $data['utility2_slot'] ?? '',
            utility2_slot_quantity: $data['utility2_slot_quantity'] ?? 0,
            bag_slot: $data['bag_slot'] ?? '',
            task: $data['task'] ?? '',
            task_type: $data['task_type'] ?? '',
            task_progress: $data['task_progress'] ?? 0,
            task_total: $data['task_total'] ?? 0,
            inventory_max_items: $data['inventory_max_items'] ?? 0,
            inventory: collect($data['inventory'] ?? [])
                ->map(fn($item) => InventorySlot::fromArray($item)),
        );
    }

    /**
     * @return int
     */
    public function currentInventoryCount(): int
    {
        return collect($this->inventory)->sum('quantity');
    }

    /**
     * @return bool
     */
    public function isInventoryFull(): bool
    {
        return $this->currentInventoryCount() >= $this->inventory_max_items;
    }
}

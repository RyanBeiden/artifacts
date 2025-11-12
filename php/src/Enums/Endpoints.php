<?php

namespace App\Enums;

enum Endpoints: string
{
    /**
    |----------------------------------------------------------------------------------------------
    | Server
    |----------------------------------------------------------------------------------------------
    */
    /** @see https://api.artifactsmmo.com/docs/#/operations/get_server_details__get */
    case ServerDetails = '/';

    /**
    |----------------------------------------------------------------------------------------------
    | My Account
    |----------------------------------------------------------------------------------------------
    */
    /** @see https://api.artifactsmmo.com/docs/#/operations/get_account_details_my_details_get */
    case MyDetails = 'my/details';

    /**
    |----------------------------------------------------------------------------------------------
    | Accounts
    |----------------------------------------------------------------------------------------------
    */
    /** @see https://api.artifactsmmo.com/docs/#/operations/get_account_achievements_accounts__account__achievements_get */
    case AccountAchievements = 'accounts/{account}/achievements';

    /**
    |----------------------------------------------------------------------------------------------
    | My Characters
    |----------------------------------------------------------------------------------------------
    */
    /** @see https://api.artifactsmmo.com/docs/#/operations/get_my_characters_my_characters_get */
    case MyCharacters = 'my/characters';
    /** @see https://api.artifactsmmo.com/docs/#/operations/action_move_my__name__action_move_post */
    case Move = 'my/{name}/action/move';
    /** @see https://api.artifactsmmo.com/docs/#/operations/action_gathering_my__name__action_gathering_post */
    case Gathering = 'my/{name}/action/gathering';
    /** @see https://api.artifactsmmo.com/docs/#/operations/action_deposit_bank_item_my__name__action_bank_deposit_item_post */
    case DepositItem = 'my/{name}/action/bank/deposit/item';

    /**
    |----------------------------------------------------------------------------------------------
    | Characters
    |----------------------------------------------------------------------------------------------
    */
    /** @see https://api.artifactsmmo.com/docs/#/operations/get_character_characters__name__get */
    case Characters = 'characters';

    /**
    |----------------------------------------------------------------------------------------------
    | Resources
    |----------------------------------------------------------------------------------------------
    */
    /** @see https://api.artifactsmmo.com/docs/#/operations/get_all_resources_resources_get */
    case AllResources = 'resources';

    /**
    |----------------------------------------------------------------------------------------------
    | Maps
    |----------------------------------------------------------------------------------------------
    */
    /** @see https://api.artifactsmmo.com/docs/#/operations/get_all_maps_maps_get */
    case AllMaps = 'maps';
}

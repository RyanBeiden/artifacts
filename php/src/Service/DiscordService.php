<?php

namespace App\Service;

use App\Enums\Endpoints;
use Discord\Builders\Components\ActionRow;
use Discord\Builders\Components\Button;
use Discord\Builders\Components\TextDisplay;
use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Guild\Emoji;
use Discord\Parts\Interactions\ApplicationCommand;
use Discord\WebSockets\Event;
use Discord\WebSockets\Intents;

class DiscordService
{
    private $emojis = [
        'men1' => '1434970895885275287',
        'men2' => '1434976358244810933',
        'men3' => '1434976216867147957',
        'women1' => '1434976218423365703',
        'women2' => '1434974150459527168',
        'women3' => '1434976359901565009',
    ];

    private Discord $discord;
    private ApiService $apiService;

    /**
     * @param string $token
     * @param int $intents
     */
    public function __construct(string $token, ApiService $apiService)
    {
        echo PHP_EOL . "Starting Discord bot..." . PHP_EOL . PHP_EOL;

        $this->discord = new Discord([
            'token' => $token,
            'intents' => [Intents::GUILD_MESSAGES, Intents::MESSAGE_CONTENT],
        ]);

        $this->apiService = $apiService;
    }

    /**
     * @return void
     */
    public function run(): void
    {
        $this->discord->on('init', function ($discord) {
            echo PHP_EOL . "Discord bot is ready!" . PHP_EOL . PHP_EOL;

            $discord->on(Event::INTERACTION_CREATE, function (ApplicationCommand $command) {
                if ($command->data->name === 'mining') {
                    $builder = new MessageBuilder();

                    $builder->setIsComponentsV2Flag();

                    $textDisplay = TextDisplay::new("Select your character to begin mining");
                    $builder->addComponent($textDisplay);

                    $actionRow = ActionRow::new();

                    $components = $this->apiService->get(Endpoints::MyCharacters)
                        ->map(function ($character) {
                            $button = Button::new(Button::STYLE_SECONDARY, $character['name']);

                            $skin = $character['skin'] ?? '';

                            if (isset($this->emojis[$skin])) {
                                $emoji = new Emoji($this->discord);

                                $emoji->id = $this->emojis[$skin];
                                $emoji->name = $skin;
                                $emoji->animated = false;

                                $button->setEmoji($emoji);
                            }

                            $name = $character['name'];
                            $level = $character['mining_level'];

                            $button->setLabel("{$name} (Lvl {$level})");

                            return $button;
                        })->toArray();

                    $actionRow->addComponents($components);

                    $builder->addComponent($actionRow);
                    $command->respondWithMessage($builder);
                }
            });
        });
    }
}

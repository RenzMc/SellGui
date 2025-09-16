# SellGUI Plugin

A PocketMine-MP plugin that provides a graphical user interface for selling items with integrated SellAll functionality.

## Features

- Sell items through an intuitive GUI interface
- Sell items in hand with `/sell hand`
- Sell all items in inventory with `/sell all` or `/sell inv`
- Sell specific groups of items (e.g., ores) with `/sell ores`
- Configurable item prices and messages
- Support for multiple economy plugins (EconomyAPI, BedrockEconomy)

## Commands

- `/sellgui` - Opens the selling GUI interface (alias: `/sellmenu`)
- `/sell hand` - Sells the item in your hand
- `/sell all` - Sells all sellable items in your inventory
- `/sell inv` - Same as `/sell all`
- `/sell ores` - Sells all ore items in your inventory
- `/sell reload` - Reloads the plugin configuration (requires `sellall.reload` permission)

## Permissions

- `sellgui.command` - Allows use of the `/sellgui` command (default: true)
- `sellall.command` - Allows use of the `/sell` command (default: true)
- `sellall.reload` - Allows reloading the plugin configuration (default: op)

## Configuration

### config.yml
This file contains the prices for each item and group configurations.

### messages.yml
This file contains all messages displayed by the plugin.

### settings.yml
This file contains plugin settings, including the economy provider.

## Installation

1. Download the plugin
2. Place it in your server's `plugins` folder
3. Restart the server
4. Configure the plugin in the `config.yml`, `messages.yml`, and `settings.yml` files

## Credits

- Original SellGUI plugin by Renz-mc
- Original SellAll plugin by AndreasHGK
- InvMenu library by Muqsit

## License

This plugin is licensed under the MIT Licenses.

# Dungeon Crawler Game

A PHP-based command-line dungeon crawler game where you explore dungeons, fight monsters, and collect weapons.

## Prerequisites

-   **Docker**: [Install Docker](https://docs.docker.com/get-started/get-docker/) (recommended)
-   **PHP**: Version 8.1+ (for manual installation)
-   **Composer**: [Install Composer](https://getcomposer.org/download/) (for manual installation)

## Installation

### Option 1: Quick Start with Docker (Recommended)

1. **Clone the repository:**

    ```bash
    git clone https://github.com/JeanDestine/dungeon-crawler-game.git
    cd dungeon-crawler-game
    ```

2. **Build and run with Docker:**
    ```bash
    docker build -t dungeon-game .
    docker run -it dungeon-game
    ```

### Option 2: Manual Installation

1. **Clone the repository:**

    ```bash
    git clone https://github.com/JeanDestine/dungeon-crawler-game.git
    cd dungeon-crawler-game
    ```

2. **Install dependencies:**

    ```bash
    composer install
    ```

3. **Run the game:**
    ```bash
    php bin/game.php
    ```

## Running Tests

### With Docker:

```bash
docker build -t dungeon-game .
docker run -it dungeon-game ./vendor/bin/phpunit tests
```

### Manual:

```bash
./vendor/bin/phpunit tests
```

## Game Features

-   Interactive command-line interface
-   Dungeon exploration with room-by-room navigation
-   Combat system
-   Monster encounters
-   Save/load game functionality

## How to Play

1. Start the game using one of the installation methods above
2. Follow the on-screen prompts to create your character
3. Navigate through the dungeon using directional commands
4. Fight monsters and collect weapons

## Project Structure

```
src/
├── Classes/          # Core game classes
├── Enums/           # Game enumerations
├── Interfaces/      # Interface definitions
└── IO/             # Input/output handling
bin/
└── game.php        # Game entry point
tests/              # Unit tests
```

## License

This project is open source and available under the MIT License.

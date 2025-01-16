{
    "name": "seuvendor/seupacote",
    "description": "Descrição do seu pacote",
    "autoload": {
        "psr-4": {
            "SeuVendor\\SeuPacote\\": "src/"
        }
    },
    "require": {
        "php": "^7.3|^8.0"
    },
    "extra": {
        "laravel": {
            "providers": [
                "SeuVendor\\SeuPacote\\SeuPacoteServiceProvider"
            ],
            "aliases": []
        }
    }
}

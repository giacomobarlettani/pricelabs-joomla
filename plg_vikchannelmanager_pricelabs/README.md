# PLG_VIKCHANNELMANAGER_PRICELABS

Integrazione Pricelabs per VikBooking su Joomla 5.x - Plugin tipo VikChannelManager

## Descrizione

Questo plugin estende VikBooking su Joomla 5.x per integrare la gestione dei prezzi e dei tassi di cambio tramite l'API di Pricelabs. Consente la sincronizzazione automatica dei tassi da Pricelabs a VikBooking.

## Struttura Progetto

```
plg_vikchannelmanager_pricelabs/
├── pricelabs.xml          # Manifest del plugin
├── pricelabs.php          # File principale del plugin
├── install.php            # Script di installazione/disinstallazione
├── src/
│   ├── Extension/
│   │   └── Pricelabs.php  # Classe extension principale
│   ├── Handler/
│   │   └── RequestHandler.php  # Handler per richieste
│   ├── Service/
│   │   ├── PricelabsApiService.php  # Servizio API
│   │   ├── RoomRateService.php      # Servizio tassi stanze
│   │   └── QueueService.php         # Servizio coda async
│   └── Helper/
│       └── Helper.php     # Utility helper
└── language/
    ├── en-GB/
    │   └── plg_vikchannelmanager_pricelabs.ini
    └── it-IT/
        └── plg_vikchannelmanager_pricelabs.ini
```

## Mapping Eventi

Il plugin implementa i seguenti event listener per VikChannelManager:

| Evento | Descrizione |
|--------|-------------|
| `onValidateAppRequestVikChannelManager` | Valida la richiesta dall'app |
| `onAuthoriseAppRequestVikChannelManager` | Autorizza la richiesta |
| `onExecuteAppRequestVikChannelManager` | Esegue la richiesta |
| `onPricelabsSetRoomRatesAppRequestVikChannelManager` | Gestisce il setting dei tassi |
| `onPricelabsAsyncQueueSummaryAppRequestVikChannelManager` | Processa la coda asincrona |
| `onCompletedAppRequestVikChannelManager` | Gestisce il completamento |

## Componenti Principali

### Extension\Pricelabs
Classe principale che orchestora i servizi e gestisce il ciclo di vita del plugin:
- Inizializza i servizi
- Configura il DI Container di Joomla
- Gestisce il logging

### Handler\RequestHandler
Elabora le richieste in ingresso:
- Valida e autorizza le richieste
- Esegue le operazioni richieste
- Gestisce gli errori

### Service\PricelabsApiService
Comunica con l'API di Pricelabs:
- Gestisce l'autenticazione
- Effettua richieste HTTP
- Elabora le risposte

### Service\RoomRateService
Gestisce i tassi delle stanze:
- Legge i tassi da Pricelabs
- Aggiorna i tassi su VikBooking
- Integra con VCMOtaRarUpdate

### Service\QueueService
Elabora le richieste in coda asincrona:
- Gestisce la coda di elaborazione
- Processa richieste in batch
- Traccia lo stato

## Configurazione

### Parametri Plugin

**Sezione Base:**
- `api_key` - Chiave API Pricelabs (obbligatoria)
- `api_url` - URL base API Pricelabs (default: https://api.pricelabs.co)
- `enabled` - Abilita/disabilita il plugin
- `debug_mode` - Abilita modalità debug

**Sezione Coda:**
- `queue_timeout` - Timeout elaborazione coda (secondi)
- `max_queue_items` - Massimo elementi per ciclo di coda
- `enable_async_processing` - Abilita elaborazione asincrona

**Sezione Logging:**
- `enable_logging` - Abilita il logging
- `log_level` - Livello minimo di log (DEBUG, INFO, WARNING, ERROR)

## Database

### Tabelle Utilizzate

#### `#__plg_vikchannelmanager_pricelabs_queue`
Coda di elaborazione asincrona
- `id` - ID primario
- `request_id` - ID richiesta univoco
- `request_type` - Tipo di richiesta
- `request_data` - Dati richiesta (JSON)
- `status` - Stato (pending, processing, completed, failed)
- `created_date` - Data creazione
- `modified_date` - Data modifica
- `attempts` - Numero tentativi
- `last_error` - Ultimo errore

#### `#__plg_vikchannelmanager_pricelabs_log`
Log dell'attività del plugin
- `id` - ID primario
- `log_level` - Livello log
- `message` - Messaggio
- `context` - Contesto aggiuntivo (JSON)
- `created_date` - Data creazione

## Installazione

1. Comprimere la cartella del plugin in un file ZIP
2. Accedere all'amministrazione di Joomla
3. Andare su **Estensioni > Installa Estensioni**
4. Caricare il file ZIP del plugin
5. Configurare i parametri del plugin
6. Abilitare il plugin

## Utilizzo

### API Endpoint di Base

```php
// Impostare i tassi
POST /pricelabs/api/set-rates
{
  "property_id": "123",
  "rates": [
    {
      "room_id": "1",
      "date": "2026-04-18",
      "rate": 99.99
    }
  ]
}

// Ottenere i tassi
GET /pricelabs/api/get-rates?property_id=123&start_date=2026-04-18&end_date=2026-04-25
```

## Dipendenze

- Joomla 5.0+
- VikBooking (versione compatibile)
- PHP 8.0+

## Classi VikBooking Integrate

- `VCMOtaRarUpdate` - Aggiornamento OTA tassi stanze
- `VBOPerformanceCleaner` - Pulizia performance
- `VCMBulkactionProcessor` - Processore azioni bulk

## Logging

I log vengono salvati in `/logs/plg_vikchannelmanager_pricelabs.php` (Joomla default logging)

## Troubleshooting

### Plugin non caricato
- Verificare che Joomla 5.x sia installato
- Controllare che il namespace sia corretto
- Verificare i permessi della cartella

### Errori API
- Controllare la chiave API
- Verificare l'URL API
- Controllare i log per dettagli

### Errori Queue
- Verificare le tabelle database
- Controllare lo spazio disponibile
- Verificare i permessi database

## Licenza

GNU General Public License version 2 or later; see LICENSE.txt

## Supporto

Per supporto, contattare il team di integrazione VikBooking.

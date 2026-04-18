# ROADMAP - Implementazione Pricelabs per VikBooking Joomla 5.x

## Fasi Completate ✅

### Fase 1: Struttura Base Plugin
- [x] Manifest XML (pricelabs.xml)
- [x] File principale plugin (pricelabs.php) con class PluginClass
- [x] Classe Extension principale (src/Extension/Pricelabs.php)
- [x] Struttura cartelle (src, language)
- [x] File di lingua EN-GB e IT-IT
- [x] Script di installazione (install.php)

### Fase 2: Core Services
- [x] PricelabsApiService - comunicazione API
- [x] RoomRateService - gestione tassi stanze
- [x] QueueService - elaborazione asincrona
- [x] RequestHandler - gestione richieste

### Fase 3: Utilità
- [x] Helper class - funzioni utility
- [x] README.md - documentazione principale

## Fasi Rimanenti 🔄

### Fase 4: Integrazione VikBooking
**Da implementare:**
- [ ] VCMOtaRarUpdate - classe per aggiornamento OTA rate
- [ ] Mapping tabelle VikBooking (rooms, rates, dates)
- [ ] Query VikBooking per fetch/update tassi
- [ ] Gestione proprietà multi-room
- [ ] Sincronizzazione tariffe per data range

**File da creare:**
- `src/Integration/VikBookingIntegration.php`
- `src/Repository/RoomRepository.php`
- `src/Repository/RateRepository.php`

### Fase 5: Validazione e Sicurezza
**Da implementare:**
- [ ] Signature verification per richieste Pricelabs
- [ ] Rate limiting e throttling
- [ ] Validazione richieste CORS
- [ ] Sanitizzazione input/output
- [ ] Protezione CSRF (se applicabile)

**File da creare:**
- `src/Security/RequestValidator.php`
- `src/Security/SignatureVerifier.php`

### Fase 6: Elaborazione Bulk
**Da implementare:**
- [ ] VCMBulkactionProcessor
- [ ] Batch processing per grandi volumi
- [ ] Retry logic con backoff
- [ ] Logging dettagliato per debug

**File da creare:**
- `src/Processor/BulkActionProcessor.php`

### Fase 7: Test e Debug
**Da implementare:**
- [ ] Unit tests
- [ ] Integration tests
- [ ] Mock Pricelabs API per testing
- [ ] Debug endpoint

**File da creare:**
- `tests/Unit/`
- `tests/Integration/`

### Fase 8: Performance e Caching
**Da implementare:**
- [ ] Cache per dati API
- [ ] Cache invalidation
- [ ] Performance monitoring
- [ ] VBOPerformanceCleaner integration

**File da creare:**
- `src/Cache/CacheManager.php`

## Note Tecniche

### DI Container Setup
Il plugin usa il Joomla\DI\Container nativo di Joomla 5 per:
- Registrazione servizi
- Dependency injection
- Lazy loading di classi

### Event Mapping
Tutti i 6 event listener sono implementati nel PluginClass:
1. `onValidateAppRequestVikChannelManager` → validation
2. `onAuthoriseAppRequestVikChannelManager` → authorization
3. `onExecuteAppRequestVikChannelManager` → execution
4. `onPricelabsSetRoomRatesAppRequestVikChannelManager` → rate setting
5. `onPricelabsAsyncQueueSummaryAppRequestVikChannelManager` → queue processing
6. `onCompletedAppRequestVikChannelManager` → completion handling

### Database Schema
Due tabelle principali:
- `#__plg_vikchannelmanager_pricelabs_queue` - coda asincrona
- `#__plg_vikchannelmanager_pricelabs_log` - logging

### Logging
Integrazione con sistema logging standard Joomla:
- Log file: `/logs/plg_vikchannelmanager_pricelabs.php`
- Livelli: DEBUG, INFO, WARNING, ERROR

## Prossimo Passo

Implementare l'integrazione VikBooking con:
1. Mappatura corretta delle tabelle di VikBooking
2. Funzioni di fetch/update per i tassi
3. Validazione dei dati VikBooking
4. Testing dell'integrazione

## Considerazioni

- **Namespace**: Joomla\Plugin\VikChannelManager\Pricelabs (PSR-4 compliant)
- **Versione Minima**: Joomla 5.0.0
- **PHP**: 8.0+
- **AutoloadLanguage**: true (file di lingua caricati automaticamente)

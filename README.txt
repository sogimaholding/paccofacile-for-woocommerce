=== Paccofacile.it for WooCommerce ===
Contributors: sogimaholding,francbarberini
Tags: paccofacile,woocommerce,shipping,spedizioni
Requires at least: 5.0.0
Tested up to: 6.4.3
Requires PHP: 7.2
Stable tag: 1.1.3
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt

Connect in few clicks your Paccofacile.it PRO's account and start saving money and time with our automatic shipping manager software.

== Description ==
Il plugin Paccofacile.it per WooCommerce aggiunge la possibilità di fornire e gestire le spedizioni del tuo negozio online con i servizi di Paccofacile.it.

Avrai bisogno di un account sul portale di Paccofacile.it e di attivare i tuoi corrieri preferiti.
Questo plugin ti permetterà di mettere a disposizione tutti i tuoi corrieri nella gestione delle spedizioni degli ordini WooCommerce, ponendo anche delle restrizioni a tuo piacimento sulle condizioni per le quali ogni corriere potrà mostrarsi disponibile nel checkout del tuo cliente.

I prezzi forniti per tutti i corrieri messi a disposizione, sono gli stessi a te riservati sul portale di Paccofacile.it: avrai la libertà di gestire le tariffe verso i tuoi clienti in completa autonomia.

Gli ordini effettuati su WooCommerce, che abbiano come scelta nel metodo di spedizione un corriere fornito da Paccofacile.it, saranno visibili in automatico anche nella tua area riservata di Paccofacile.it. Sarà possibile anche confermare e acquistare la spedizione direttamente dalla gestione ordini di WooCommerce tramite il tuo credito residuo sull’account Paccofacile.it.

Principali funzionalità:

Tariffe dedicate per le tue spedizioni con i migliori corrieri.
Libera scelta sui servizi dei corrieri da abilitare tra quelli offerti da Paccofacile.it
Gestione degli imballi a disposizione per poter ottimizzare il numero di colli utilizzati per le spedizioni. A scelta tra le tipologie: busta, scatola e pallet.
Calcolo costo di spedizione nel carrello ottimizzando in automatico il numero di colli utilizzati per il tuo ordine.
Ricezione automatica di notifiche email sugli aggiornamenti del tracking per i clienti.
Generazione e ricezione automatica via mail dei documenti di trasporto (LDV)
Pagamento automatizzato delle spedizioni direttamente dalla gestione ordini WooCommerce.
Possibilità di gestire i costi di spedizione per il cliente finale, partendo da quelli del listino dedicato all’account Paccofacile.it PRO utilizzato.


== Installation ==
Installazione dalla directory dei plugin nella bacheca di WordPress

Andare al pulsante “Aggiungi Nuovo” nella sezione Plugin
Cerca \"paccofacile.it for woocommerce”
Clicca “Installa Ora”
Attiva il plugin nella bacheca dei plugin

Caricamento del file .zip dalla bacheca di WordPress

Andare al pulsante “Aggiungi Nuovo” nella bacheca dei plugin
Vai nell’area “Upload”
Seleziona “paccofacile.it for woocommerce.zip” dal tuo computer
Clicca “Installa Ora”
Attiva il plugin nella bacheca dei plugin

Caricamento manuale tramite FTP

Scarica “paccofacile.it for woocommerce”
Estrai la cartella “paccofacile.it for woocommerce” nel tuo computer
Carica la cartella “paccofacile.it for woocommerce” nella cartella /wp-content/plugins/
Attiva il plugin nella bacheca dei plugin

Configurazione

- Accedi al portale Paccofacile.it con il tuo account o registrati alla versione PRO
- Naviga nella sezione Integrazioni -> WooCommerce e SCARICA PLUGIN
- Genera le chiavi API cliccando su Integrazioni -> API PACCOFACILE.IT e cliccando su API KEY LIVE
- Nel pannello di controllo del tuo sito web, dopo aver attivato il plugin, naviga nella sua pagina di configurazione WooCommerce -> Paccofacile.it
- Incolla le tue credenziali API nei campi appositi e salva. 
- Naviga nella tab “Servizi di spedizione” e clicca sul bottone “Aggiungi servizio”
- Scegli quali servizi di spedizione attivare per il tuo negozio
- Naviga nella tab “imballi” e clicca sul bottone “Aggiungi imballo”.
- Aggiungi tutti i tipi di imballi(*) che hai a disposizione per le tue spedizioni, dando loro un nome.

== Utilizzo di servizi esterni ==
Il plugin fa uso di servizi di terze parti (Paccofacile.it - https://www.paccofacile.it) tramite chiamate API.
I dati inviati dal plugin a tale servizio vengono utilizzati per lo scopo di configurare, salvare, gestire e pagare servizi di spedizione su Paccofacile.it e
vengono utilizzati e memorizzati dal servizio nelle modalità indicate su termini e condizioni nella pagina https://www.paccofacile.it/termini-condizioni e 
sono trattati secondo la privacy policy indicata nella pagina https://www.paccofacile.it/privacy-policy.
La url utilizzata per la comunicazione con il servizio è https://paccofacile.tecnosogima.cloud/live/v1/service/.

Il plugin integra la libreria OpenLayers (https://openlayers.org) per la visualizzazione di una mappa interattiva dei luoghi disponibili per la spedizione della merce.
È disponibile il codice sorgente della libreria in uso al seguente link: https://github.com/openlayers/openlayers/releases/tag/v6.15.1.
La libreria OpenLayers fa uso di servizi di terze parti verso il dominio https://www.openstreetmap.org per il recupero delle coordinate delle località da visualizzare e delle immagini per la visualizzazione della mappa.
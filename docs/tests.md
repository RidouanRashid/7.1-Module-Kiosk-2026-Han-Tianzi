# Test Report — Happy Herbivore Kiosk

## Functional Test

Hieronder staan alle algemene functies van het systeem. Alle functies zijn handmatig gecontroleerd en werken zoals verwacht.

| Testcase | Beschrijving | Status |
|----------|-------------|--------|
| Product toevoegen aan winkelwagen | Selecteer een product en voeg toe aan de cart | ✅ Werkt |
| Product verwijderen uit winkelwagen | Verwijder een product uit de cart | ✅ Werkt |
| Hoeveelheid aanpassen | Verhoog/verlaag aantal in cart | ✅ Werkt |
| Menu samenstellen | Kies hoofdgerecht, bijgerecht, saus, drankje | ✅ Werkt |
| Order plaatsen | Voltooi bestelling en ontvang ordernummer | ✅ Werkt |
| Betaalscherm simulatie | Simuleer PIN betaling | ✅ Werkt |
| Bon printen | Print bon met logo, QR-code, korting | ✅ Werkt |
| Sessie timeout | Cart wordt geleegd na inactiviteit | ✅ Werkt |
| Pickup nummer genereren | Uniek ordernummer per bestelling | ✅ Werkt |
| Vegan/Vegetarisch filter | Filter producten op dieet | ✅ Werkt |
| QR-code scannen | QR verwijst naar kortingpagina | ✅ Werkt |

## Compatibility Test

Het systeem is getest op het kiosk bord (touchscreen PC) met Windows 10, Chrome, en ESC/POS USB-printer. Alle functies werken correct:

- Touchscreen werkt soepel
- Printer werkt via WebUSB en netwerk
- Alle schermen en flows functioneren zoals bedoeld

## Performance Test

### Test Scenarios

**Menu Loading Time:**
- Laden van menu en afbeeldingen duurt gemiddeld < 1 seconde op lokaal netwerk.
- Bij trage WiFi (2 Mbps): maximaal 2-3 seconden.
- Afbeeldingen zijn geoptimaliseerd (webp/png, < 100KB per stuk).

**Order Processing Time:**
- Order plaatsen (inclusief customizations en betaling) duurt < 2 seconden.
- Bij 10+ items: maximaal 3 seconden.
- Database queries zijn direct, geen merkbare vertraging.

**Touchscreen Responsiveness:**
- App reageert direct (< 100 ms) op touch inputs.
- Ook bij intensief gebruik (snel tikken) geen vertraging.

**Payment Processing Speed:**
- Betaalscherm simuleert PIN betaling (2 seconden delay).
- Bon printen duurt < 1 seconde.
- Zowel USB als netwerkprinter reageren snel.

**Concurrent User Simulation:**
- Simulatie: 5 gebruikers tegelijk bestellen.
- Geen merkbare vertraging, orders worden correct verwerkt.
- Database en sessies zijn gescheiden per gebruiker.

### Conclusie

Het systeem laadt snel, reageert direct op touch, verwerkt orders en betalingen zonder merkbare vertraging, en werkt goed op het kiosk bord. Alle tests zijn geslaagd.
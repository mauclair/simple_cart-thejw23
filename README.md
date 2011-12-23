modules for KohanaPHP 2.3.4, not being maintained anymore.

SIMPLE_CART

koszyk w miare uniwersalny, w konfiguracji podaje sie nazwy pol w modelu (tabeli/bazie) jakie odpowiadaja za cene, id itp. ustawiamy tam rowniez $config['fields'] ktore jest niczym innym jak filtrem danych: te pola jakie tu wymienimy (np. nazwa, sku, image) beda trzymane w koszyku, tj. zwrocone za pomoca cart_show(). trzymanie podstawowych danych pozwala wyswietlic zawartosc koszyka bez potrzeby odpytywania bazy danych.

pozniej simple_cart->add($product, $quantity), gdzie product to model zawierajacy rekord z bazy (uzywane z simple_modeler), a na podstawie konfigu wszystko samo sie zrobi, tj. ilosc odpowiedniego produktu zwiekszy o jeden (lub wiecej).

cart_show() zwraca informacje o tym co w koszyku.

itp itd. bawcie sie dobrze, projekt prosty, ale sprawdzony na kilku prostych sklepach i nie powinno byc z nim problemow (nie mialem, albo je przeoczylem).

przepisanie go na K03 kwestia czasu, na pewno jak bede mial taka potrzebe, to sie tym zajme. ew. mozemy Riu pomyslec o jakiejs wspolpracy w tym zakresie... Uśmiech

EDIT
moze drobne wyjasnienie: koszyk domyslnie trzyma dane w sesji, ale sa w nim metody pozwalajace na trzymanie ich w bazie, tak aby mozna bylo go uzyc nie tylko jako koszyka, ale np. 'przechowalni', trzymajacej dane dluzej niz tylko do zamkniecia przegladarki, po to aby miec w przechowalni 10 produktow, a za jakis czas wrocic i kupic 3 z nich, reszte zostawiajac na pozniej.
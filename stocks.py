import time
import requests
from bs4 import BeautifulSoup
from pymongo import MongoClient
from requests.exceptions import RequestException

client = MongoClient('mongodb://localhost:27017')
collection = client.stock_data.most_active_stocks

url = 'https://finance.yahoo.com/most-active'

while True:
    try:
        response = requests.get(url)

        response.raise_for_status()
    except RequestException as e:
        print(f'Request failed: {e}')
        time.sleep(3 * 60)  
        continue

    soup = BeautifulSoup(response.text, 'html.parser')

    table = soup.find('table')

    stock_data = []
    for row in table.tbody.find_all('tr'):
        cells = row.find_all('td')
        symbol = cells[0].text
        name = cells[1].text
        price = cells[2].text
        change = cells[3].text
        volume = cells[6].text

        stock_data.append({'Symbol': symbol, 'Name': name, 'Price': price, 'Change': change, 'Volume': volume})

    collection.delete_many({})

    collection.insert_many(stock_data)

    time.sleep(3 * 60)

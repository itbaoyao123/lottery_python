# coding=UTF-8
import requests
from urllib.parse import urlencode#解析编码的库
import pandas as pd
import pickle
import json
import datetime
import os
import sys

# print (sys.path[0])
# print ("123123")

url = 'http://www.cwl.gov.cn/cwl_admin/front/cwlkj/search/kjxx/findDrawNotice?'

params = {
    'name': 'ssq',
    'issueCount': '1',
    'issueStart':'',
    'issueEnd': '',
    'dayStart': '',
    'dayEnd': '',
}

url = url+urlencode(params)
resp = requests.get(url = url)
codes,dates,numbers,sales,first_types,second_types,third_types = [[] for i in range(7)]
for ssq in resp.json()['result']:
    code = ssq['code']
    codes.append(code)
    date = ssq['date']
    dates.append(date)
    number = ssq['red']+','+ssq['blue']
    numbers.append(number)
    sale = ssq['sales']
    sales.append(sale)
    first_type = ssq['prizegrades'][0]['type'], ssq['prizegrades'][0]['typenum'], ssq['prizegrades'][0]['typemoney']
    first_types.append(first_type)
    second_type = ssq['prizegrades'][1]['type'], ssq['prizegrades'][1]['typenum'], ssq['prizegrades'][1]['typemoney']
    second_types.append(second_type)
    third_type = ssq['prizegrades'][2]['type'], ssq['prizegrades'][2]['typenum'], ssq['prizegrades'][2]['typemoney']
    third_types.append(third_type)
#     print(code,date,number,sales,first_type,second_type,third_type)
dic = {'code':codes,'date':dates,'number':numbers,'sales':sale,'first_type':first_types,'second_type':second_types,'third_type':third_types}#创建字典为创建多维表做准备
json_dic =json.dumps(dic,ensure_ascii=False)
# frame = pd.DataFrame(dic)
# frame.to_csv('ssq.txt')#可以选择将数据储存到CSV文件

# i = datetime.datetime.now()
print(dic);

curr_time = datetime.datetime.now()
timestamp=datetime.datetime.strftime(curr_time,'%Y-%m-%d')
path = sys.path[0]+'/ssq/'+timestamp
if os.path.exists(path):
    pass
else:
    os.mkdir(path)

with open(path+'/ssq.txt','w',encoding='utf8') as file:
    file.write(json_dic)

print(timestamp)


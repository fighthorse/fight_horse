试题
用PHP读取excel数据写入mysql数据表

要求：
1.不要使用框架
2.导入的excel要支持.xls及.xlsx两种格式
3.导入的excel可能有重复，数据，如果遇到重复数据，需要增加个随机字符，再写入数据表。
4.对重复的数据或导入失败的数据要给出提示。
  比如成功导入x条，失败x条、重复x条，失败的数据是什么，重复的数据有哪些等


数据表 
    users   
字段
    id   username  addtime  
	
用户名不允许重复

excel数据

类似这样
username
aaa
bbb
ccc
aaa
ddd


仓库地址 https://github.com/9allenzhao/tanxinchao.git  
如果仓库打不开，完成后发邮件到 eleven.3536@gmail.com
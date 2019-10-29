import os
import argparse
import hashlib
from pyinotify import WatchManager, Notifier,ProcessEvent
from pyinotify import IN_DELETE, IN_CREATE, IN_MOVED_TO, IN_ATTRIB, IN_MODIFY

dir_path = ""

class EventHandler(ProcessEvent):
		"""事件处理"""
		#创建新文件，自动删除
		def process_IN_CREATE(self, event):
			print "[!] Create : " + event.pathname
			DeleteFileOrDir(event.pathname)

		#文件被删除，如rm命令，自动恢复原文件
		def process_IN_DELETE(self, event):
			print "[!] Delete : " + event.pathname
			# CanNotDel(event.pathname)

		#文件属性被修改，如chmod、chown命令
		def process_IN_ATTRIB(self, event):
			print "[!] Attribute been modified:" + event.pathname

		#文件被移来，如mv、cp命令，自动删除
		def process_IN_MOVED_TO(self, event):
			print "[!] File or dir been moved to here: " + event.pathname
			DeleteFileOrDir(event.pathname)

		#文件被修改，如vm、echo命令，自动恢复原文件
		def process_IN_MODIFY(self, event):
			print "[!] Modify : " + event.pathname
			CanNotModify(event.pathname)

def DeleteFileOrDir(target):
	if os.path.isdir(target):
		fileslist = os.listdir(target)
		try:
			os.system('cp ' + str(target) + ' /tmp/evil_files/')
		except:
			pass
		for files in fileslist:
			DeleteFileOrDir(target + "/" + files)
		try:
			os.rmdir(target)
			print "	 >>> Delete directory successfully: " + target
		except:
			print "	 [-] Delete directory failed: " + target
	if os.path.isfile(target):
		try:
			os.system('cp ' + str(target) + ' /tmp/evil_files/')
		except:
			pass
		try:
			os.remove(target)
			print "	 >>> Delete file successfully" + target
		except:
			print "	 [-] Delete file failed:  " + target

def get_file_md5(f):
	m = hashlib.md5()
	while True:
		#如果不用二进制打开文件，则需要先编码
		#data = f.read(1024).encode('utf-8')
		data = f.read(1024)  #将文件分块读取
		if not data:
			break
		m.update(data)
	return m.hexdigest()

def CompareFile(file1, file2):
	with open(file1, 'rb') as f1, open(file2, 'rb') as f2:
		file1_md5 = get_file_md5(f1)
		file2_md5 = get_file_md5(f2)

	if file1_md5 != file2_md5:
		return True
	else:
		return False

def CanNotModify(target):
	if os.path.exists(target):
		if os.path.isfile(target):
			print "dir path: " + dir_path
			target_new = "/tmp/backup/" + str(target).split(dir_path)[1]
			isChanged = CompareFile(str(target), target_new)
			if isChanged:
				try:
					os.system('cp ' + str(target) + ' /tmp/evil_files/')
				except Exception, e:
					pass
				try:
					os.system("cat " + target_new + " > " + str(target))
					print "	 >>> Recover file successfully"
				except Exception, e:
					print "	 [-] Recover file failed:  " + target

def CanNotDel(target):
	if not os.path.exists(target):
		try:
			target_new = "/tmp/backup/" + str(target).split(dir_path)[1]
			os.system("cat " + target_new + " > " + str(target))
			print "	 >>> Recover file successfully"
		except Exception, e:
			print "	 [-] Recover file failed:  " + target

def Monitor(path):
	wm = WatchManager()
	mask = IN_DELETE | IN_CREATE | IN_MOVED_TO | IN_ATTRIB | IN_MODIFY
	notifier = Notifier(wm, EventHandler())
	wm.add_watch(path, mask,rec=True)
	print '[+] Now Starting Monitor:  %s'%(path)
	while True:
		try:
			notifier.process_events()
			if notifier.check_events():
				notifier.read_events()
		except KeyboardInterrupt:
			notifier.stop()
			break
						
if __name__ == "__main__":
	try:
		os.system('mkdir /tmp/evil_files/')
		os.system('mkdir /tmp/backup/')
	except:
		print 'Oh.'
	parser = argparse.ArgumentParser(
		usage="%(prog)s -w [path]",
		description=('''
			Introduce：Simple Directory Monitor!  by ssooking''')
	)
	parser.add_argument('-w','--watch',action="store",dest="path",default="/var/www/html/",help="directory to watch,default is /var/www/html")
	args=parser.parse_args()
	dir_path = str(args.path)
	print "dir path: " + dir_path
	cmd = 'cp -r ' + str(args.path)
	print "dir path: " + dir_path
	cmd = 'cp -r ' + str(args.path) + '* /tmp/backup/'
	os.system(cmd)
	Monitor(args.path)
# pi-radio
Webradio for Raspberry PI

Start application with

    docker-compose -f docker-compose.prod.yml up





# setup external USB soundcard
/boot/config.txt

	# find this line and comment out
	#dtparam=audio=on


/lib/modprobe.d/aliases.conf

	# find this line and comment out
	#options snd-usb-audio index=-2



# set volume to 100% 

	#get NAME of soundcart
	cat /proc/asound/cards
	

    #set volumne 100%
	amixer -c 0 sset 'Speaker' 100%


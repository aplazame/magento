[ ![Image](https://aplazame.com/static/img/banners/banner-728-white-magento.png "Aplazame") ](https://aplazame.com "Aplazame")

[![Build Status](http://drone.aplazame.com/api/badge/github.com/aplazame/magento/status.svg?branch=master)](http://drone.aplazame.com/github.com/aplazame/magento)

**Compatible with**

Magento CE 1.4.0.1+

### Configure

1. Log in to your Magento Admin panel.
2. Visit System > Cache Management. Then, click "Flush Magento Cache".
3. Visit System > Configuration > Payment Methods > Aplazame.
4. Provide your API tokens, public and secret key.
 
![config](docs/config.png)


#### Developers

**To install using [modgit](https://github.com/jreinke/modgit)**

```
modgit init
modgit -i extension/:. add Magento_Aplazame https://github.com/aplazame/magento
```

> to update:

```
modgit update Magento_Aplazame
```

**To install using [modman](https://github.com/colinmollenhour/modman)**

```
modman clone https://github.com/aplazame/magento
```

> to update:

```
modman update Magento_Aplazame
```


#### Live demo

This is the online demo for uses to test Aplazame and its features. 

[http://magento.aplazame.com](http://magento.aplazame.com)


#### Install Magento

It is easy to deploy Magento with [Ansible](http://www.ansible.com/home)!

[https://github.com/aplazame/ansible-magento](https://github.com/aplazame/ansible-magento)


#### Release history

For new features check [this](HISTORY.md).


#### Help

**Have a question about Aplazame?**

For any support request please drop us an email at email soporte@aplazame.com.

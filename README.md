[ ![Image](https://aplazame.com/static/img/banners/banner-728-white.png "Aplazame") ](https://aplazame.com "Aplazame")

**Compatible with**

Magento CE 1.4.0.1+

## Install

**To install using [modgit](https://github.com/jreinke/modgit)**

```
modgit init
modgit -i extension/:. add Magento_Aplazame https://github.com/aplazame/magento
```
to update:
```
modgit update Magento_Aplazame
```

**To install using [modman](https://github.com/colinmollenhour/modman)**

```
modman clone https://github.com/aplazame/magento
```
to update:
```
modman update Magento_Aplazame
```

## Configure

1. Log in to your Magento Admin panel.
2. Visit System > Cache Management. Then, click "Flush Magento Cache".
3. Visit System > Configuration > Payment Methods > Aplazame.
4. Select sandbox or production mode.
5. Provide your 2 api tokens, public and secret key.
 
![config](docs/config.png)

## Help

**Have a question about Aplazame?**

Aplazame support team can help you get the answers you need about this plugin by email soporte@aplazame.com.

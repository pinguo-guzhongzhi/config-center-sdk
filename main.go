package main

import (
	"code.aliyun.com/MIG-server/micro-base/config"
	"code.aliyun.com/MIG-server/micro-base/microclient"
	"code.aliyun.com/MIG-server/micro-base/runtime"
	"encoding/json"
	"fmt"
	"github.com/urfave/cli/v2"
	"io/ioutil"
	"os"
	"time"
)

func main() {

	app := cli.NewApp()
	app.Flags = []cli.Flag{
		&cli.StringFlag{
			Name:     "server",
			Usage:    "config server api url, env CONFIG_SERVER_URL",
			Required: true,
			EnvVars:  []string{"CONFIG_SERVER_URL"},
		},
		&cli.StringFlag{
			Name:     "clientId",
			Usage:    "the client id of the service, env CONFIG_CLIENT_ID",
			Required: true,
			EnvVars:  []string{"CONFIG_CLIENT_ID"},
		},
		&cli.StringFlag{
			Name:     "secret",
			Usage:    "secret key from config center, env CONFIG_CLIENT_SECRET",
			Required: true,
			EnvVars:  []string{"CONFIG_CLIENT_SECRET"},
		},
		&cli.StringFlag{
			Name:     "env",
			Usage:    "client env, env CONFIG_CLIENT_ENV",
			Required: true,
			EnvVars:  []string{"CONFIG_CLIENT_ENV"},
			Value:    "dev",
		},
	}
	app.Commands = []*cli.Command{
		&cli.Command{
			Name: "config:show",
			Action: func(ctx *cli.Context) error {
				client := &Client{}
				err := client.ParseCtx(ctx)
				if err != nil {
					return err
				}
				return client.Show()
			},
		},
		&cli.Command{
			Name: "config:export",
			Action: func(ctx *cli.Context) error {
				client := &Client{}
				err := client.ParseCtx(ctx)
				if err != nil {
					return err
				}
				return client.Export()
			},
			Flags: []cli.Flag{
				&cli.StringFlag{
					Name:     "fileName",
					Usage:    "export the config to file",
					Required: true,
				},
			},
		},
	}
	app.Run(os.Args)
}

type Client struct {
	ClientId  string
	Secret    string
	ServerUrl string
	Env       string
	FileName  string
}

func (s *Client) ParseCtx(ctx *cli.Context) error {
	s.ClientId = ctx.String("clientId")
	s.Secret = ctx.String("secret")
	s.ServerUrl = ctx.String("server")
	s.Env = ctx.String("env")
	s.FileName = ctx.String("fileName")
	fmt.Println("s.clientId", s.ClientId, s.Secret, s.ServerUrl, s.Env)
	runtime.SetDebug(true)
	return nil
}

func (s *Client) Export() error {
	fmt.Println("Export")
	data, err := s.getData()
	if err != nil {
		return err
	}
	js, err := json.MarshalIndent(data, "", "    ")
	fmt.Println(string(js))
	return ioutil.WriteFile(s.FileName, js, os.ModePerm)
}

func (s *Client) Show() error {
	data, err := s.getData()
	if err != nil {
		return err
	}
	for k, v := range data {
		fmt.Println(k, ": ", v)
	}
	return nil
}

func (s *Client) getData() (map[string]interface{}, error) {
	loaderOptions := config.NewOptions(config.ConfigReloadDurationOption(time.Second*600),
		config.NewCallBackOption(func(loader *config.Loader) {

		}))
	_, err :=
		config.InitLoader(s.Env, "/tmp/1",
			loaderOptions,
			microclient.HttpCallUrlOption(s.ServerUrl),
			microclient.NewClientSecretOption(s.Secret),
			microclient.ClientCallTypeOption(microclient.ClientCallTypeHttp),
			microclient.NewClientIdOption(s.ClientId))
	if err != nil {
		return nil, err
	}

	data := make(map[string]interface{})
	for _, key := range config.Config().AllKeys() {
		data[key] = config.Config().Get(key)
	}
	return data, nil
}
